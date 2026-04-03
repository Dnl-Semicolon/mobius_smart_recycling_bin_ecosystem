import { useState, useEffect, useRef } from 'react'
import { api } from './api'
import { useWebcam } from './hooks/useWebcam'
import { useQRScanner } from './hooks/useQRScanner'

type InputMethod = 'cup_slot' | 'lid_slot' | 'straw_slot' | 'general_intake'
type AppState = 'select' | 'idle' | 'active' | 'summary'

interface Detection {
  id: number
  waste_type: string
  input_method: string
  confidence: number
  detected_brand: string | null
  created_at: string
}

interface BinOption {
  serial_number: string
  fill_level: number
  weight_grams: number
  outlet: string | null
  brand: string | null
  address: string | null
}

interface SessionSummary {
  items_count: number
  cup_rinsed: boolean
  points_earned: number
  user_linked: boolean
}

export default function App() {
  const { videoRef, canvasRef, isReady, error, captureFrame } = useWebcam()
  const { scanForQR } = useQRScanner()

  const [state, setState] = useState<AppState>('select')
  const [serial, setSerial] = useState<string>('')
  const [bins, setBins] = useState<BinOption[]>([])
  const [sessionId, setSessionId] = useState<number | null>(null)
  const [binInfo, setBinInfo] = useState<any>(null)
  const [detections, setDetections] = useState<Detection[]>([])
  const [inputMethod, setInputMethod] = useState<InputMethod>('cup_slot')
  const [cupRinsed, setCupRinsed] = useState(false)
  const [linkedUser, setLinkedUser] = useState<string | null>(null)
  const [summary, setSummary] = useState<SessionSummary | null>(null)
  const [detecting, setDetecting] = useState(false)
  const [lastResult, setLastResult] = useState<string | null>(null)
  const [showBinTooltip, setShowBinTooltip] = useState(false)
  const qrIntervalRef = useRef<ReturnType<typeof setInterval> | null>(null)

  // Load bins on mount
  useEffect(() => {
    const envSerial = import.meta.env.VITE_BIN_SERIAL
    if (envSerial) {
      setSerial(envSerial)
      setState('idle')
    } else {
      fetchBins()
    }
  }, [])

  const fetchBins = async () => {
    try {
      const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1'
      const res = await fetch(`${API_BASE}/bins/active`)
      const data = await res.json()
      setBins(data.bins || [])
    } catch { /* ignore */ }
  }

  const selectBin = (s: string) => {
    setSerial(s)
    setState('idle')
  }

  // Background QR scanning
  useEffect(() => {
    if (state !== 'active' || !isReady || linkedUser) return

    qrIntervalRef.current = setInterval(() => {
      if (!videoRef.current || !canvasRef.current) return
      const qrData = scanForQR(videoRef.current, canvasRef.current)
      if (qrData) {
        const match = qrData.match(/mobius:user:(\d+)/)
        if (match && sessionId) {
          const userId = parseInt(match[1])
          api.linkUser(serial, sessionId, userId).then((res) => {
            if (res.user) setLinkedUser(res.user.name)
          })
        }
      }
    }, 1000)

    return () => { if (qrIntervalRef.current) clearInterval(qrIntervalRef.current) }
  }, [state, isReady, linkedUser, sessionId, serial, scanForQR, videoRef, canvasRef])

  const startSession = async () => {
    const res = await api.startSession(serial)
    if (res.session_id) {
      setSessionId(res.session_id)
      setBinInfo(res.bin)
      setDetections([])
      setCupRinsed(false)
      setLinkedUser(null)
      setSummary(null)
      setLastResult(null)
      setState('active')
    }
  }

  const detectItem = async () => {
    if (!sessionId || detecting) return
    setDetecting(true)

    const image = captureFrame()
    if (!image) { setDetecting(false); return }

    const res = await api.detect(serial, sessionId, image, inputMethod)

    if (res.detection) {
      setDetections((prev) => [res.detection, ...prev]) // newest first
      setLastResult(
        `${res.detection.waste_type} (${res.detection.confidence}%)${res.detection.detected_brand ? ` — ${res.detection.detected_brand}` : ''}`
      )
      // Reset cup_rinsed for next item
      setCupRinsed(false)
    } else {
      setLastResult('No item detected — try again')
    }

    // Update bin info with latest fill level
    if (res.bin) {
      setBinInfo((prev: any) => ({ ...prev, ...res.bin }))
    }

    setDetecting(false)
  }

  const toggleRinse = async () => {
    if (!sessionId) return
    await api.markRinsed(serial, sessionId)
    setCupRinsed(true)
  }

  const endSession = async () => {
    if (!sessionId) return
    const res = await api.endSession(serial, sessionId)
    if (res.session) {
      setSummary(res.session)
      setState('summary')
    }
  }

  const resetToIdle = () => {
    setState('idle')
    setSessionId(null)
    setDetections([])
    setCupRinsed(false)
    setLinkedUser(null)
    setSummary(null)
    setLastResult(null)
  }

  const goToSelect = () => {
    resetToIdle()
    setSerial('')
    fetchBins()
    setState('select')
  }

  useEffect(() => {
    if (state === 'summary') {
      const timer = setTimeout(resetToIdle, 10000)
      return () => clearTimeout(timer)
    }
  }, [state])

  const inputMethods: { key: InputMethod; label: string; icon: string }[] = [
    { key: 'cup_slot', label: 'Cup', icon: '🥤' },
    { key: 'lid_slot', label: 'Lid', icon: '⭕' },
    { key: 'straw_slot', label: 'Straw', icon: '📏' },
    { key: 'general_intake', label: 'General', icon: '📦' },
  ]

  // Bin selector screen
  if (state === 'select') {
    return (
      <div className="min-h-screen bg-gray-50 flex flex-col items-center justify-center p-8">
        <div className="w-8 h-8 bg-teal-600 rounded-lg flex items-center justify-center text-white font-bold text-sm mb-4">M</div>
        <h1 className="text-2xl font-bold text-gray-900 mb-2">Mobius Bin Simulator</h1>
        <p className="text-gray-500 mb-8">Select a bin to simulate</p>
        {bins.length === 0 ? (
          <p className="text-gray-400">No active bins found. Pair a bin first.</p>
        ) : (
          <div className="grid gap-3 w-full max-w-lg">
            {bins.map((b) => (
              <button
                key={b.serial_number}
                onClick={() => selectBin(b.serial_number)}
                className="bg-white border rounded-xl p-4 text-left hover:border-teal-500 hover:shadow transition"
              >
                <div className="flex justify-between items-start">
                  <div>
                    <div className="font-mono font-semibold text-gray-900">{b.serial_number}</div>
                    <div className="text-sm text-gray-500 mt-1">{b.outlet} — {b.brand}</div>
                    <div className="text-xs text-gray-400 mt-0.5">{b.address}</div>
                  </div>
                  <div className="text-right">
                    <div className="text-sm font-medium">{b.fill_level}%</div>
                    <div className="w-16 h-2 bg-gray-200 rounded-full mt-1">
                      <div
                        className="h-2 bg-teal-500 rounded-full transition-all"
                        style={{ width: `${b.fill_level}%` }}
                      />
                    </div>
                  </div>
                </div>
              </button>
            ))}
          </div>
        )}
      </div>
    )
  }

  return (
    <div className="h-screen bg-gray-50 flex flex-col overflow-hidden">
      {/* Header */}
      <header className="bg-white border-b px-6 py-3 flex items-center justify-between shrink-0">
        <div className="flex items-center gap-3">
          <button onClick={goToSelect} className="w-8 h-8 bg-teal-600 rounded-lg flex items-center justify-center text-white font-bold text-sm hover:bg-teal-700">M</button>
          <span className="font-semibold text-gray-900">Mobius Bin</span>
          <span className="text-sm text-gray-500 font-mono">{serial}</span>
        </div>
        <div className="flex items-center gap-4 text-sm relative">
          {binInfo && (
            <button
              className="flex items-center gap-2 hover:bg-gray-100 rounded-lg px-2 py-1 transition"
              onMouseEnter={() => setShowBinTooltip(true)}
              onMouseLeave={() => setShowBinTooltip(false)}
            >
              <span className="text-gray-700 font-medium">Fill: {binInfo.fill_level}%</span>
              <div className="w-20 h-2 bg-gray-200 rounded-full">
                <div
                  className={`h-2 rounded-full transition-all ${
                    binInfo.fill_level >= 75 ? 'bg-red-500' : binInfo.fill_level >= 50 ? 'bg-amber-500' : 'bg-teal-500'
                  }`}
                  style={{ width: `${binInfo.fill_level}%` }}
                />
              </div>
              <span className="text-gray-500">{binInfo.outlet} ({binInfo.brand})</span>
            </button>
          )}
          {/* Bin Health Tooltip */}
          {showBinTooltip && binInfo && (
            <div className="absolute top-full right-0 mt-2 bg-white border rounded-xl shadow-lg p-4 w-72 z-50">
              <div className="text-xs font-medium text-gray-500 uppercase mb-3">Bin Health</div>
              <div className="space-y-2 text-sm">
                <div className="flex justify-between"><span className="text-gray-500">Serial</span><span className="font-mono">{serial}</span></div>
                <div className="flex justify-between"><span className="text-gray-500">Fill Level</span><span>{binInfo.fill_level}%</span></div>
                <div className="flex justify-between"><span className="text-gray-500">Weight</span><span>{binInfo.weight_grams ?? 0}g</span></div>
                <div className="flex justify-between"><span className="text-gray-500">Outlet</span><span>{binInfo.outlet}</span></div>
                <div className="flex justify-between"><span className="text-gray-500">Brand</span><span>{binInfo.brand}</span></div>
                {binInfo.sensor_levels && (
                  <div className="pt-2 border-t">
                    <div className="text-xs text-gray-500 mb-1">Sensors</div>
                    <div className="grid grid-cols-4 gap-1">
                      {Object.entries(binInfo.sensor_levels).map(([k, v]) => (
                        <div key={k} className={`text-center text-xs py-1 rounded ${v ? 'bg-teal-100 text-teal-700' : 'bg-gray-100 text-gray-400'}`}>
                          {k.replace('level_', '')}%
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            </div>
          )}
          <div className={`w-2 h-2 rounded-full ${isReady ? 'bg-green-500' : 'bg-red-500'}`} />
        </div>
      </header>

      <main className="flex-1 flex min-h-0">
        {/* Camera Feed */}
        <div className="flex-1 relative bg-black">
          <video ref={videoRef} autoPlay playsInline muted className="w-full h-full object-cover" />
          <canvas ref={canvasRef} className="hidden" />

          {error && (
            <div className="absolute inset-0 flex items-center justify-center bg-black/80 text-white text-lg">{error}</div>
          )}

          {lastResult && state === 'active' && (
            <div className="absolute top-4 left-4 right-4 bg-white/90 rounded-lg p-3 text-center font-medium">{lastResult}</div>
          )}

          {detecting && (
            <div className="absolute inset-0 flex items-center justify-center bg-black/40">
              <div className="bg-white rounded-lg px-6 py-3 font-medium">Detecting...</div>
            </div>
          )}

          {state === 'idle' && (
            <div className="absolute inset-0 flex flex-col items-center justify-center bg-black/50 text-white">
              <h1 className="text-3xl font-bold mb-2">Welcome to Mobius</h1>
              <p className="text-lg mb-8 text-white/80">Place your recyclable items to begin</p>
              <button onClick={startSession} disabled={!isReady} className="bg-teal-600 hover:bg-teal-700 text-white px-8 py-4 rounded-xl text-xl font-semibold disabled:opacity-50">
                Start Recycling Session
              </button>
            </div>
          )}

          {state === 'summary' && summary && (
            <div className="absolute inset-0 flex flex-col items-center justify-center bg-black/60 text-white">
              <h1 className="text-3xl font-bold mb-6">Thank You!</h1>
              <div className="bg-white text-gray-900 rounded-2xl p-8 max-w-md w-full mx-4 space-y-4">
                <div className="text-center">
                  <div className="text-5xl font-bold text-teal-600">{summary.points_earned}</div>
                  <div className="text-gray-500 mt-1">points earned</div>
                </div>
                <div className="grid grid-cols-2 gap-4 text-center">
                  <div><div className="text-2xl font-bold">{summary.items_count}</div><div className="text-gray-500 text-sm">items recycled</div></div>
                  <div><div className="text-2xl font-bold">{summary.cup_rinsed ? 'Yes' : 'No'}</div><div className="text-gray-500 text-sm">cup rinsed</div></div>
                </div>
                {summary.user_linked ? (
                  <div className="bg-teal-50 text-teal-700 rounded-lg p-3 text-center text-sm">Points credited to your account</div>
                ) : (
                  <div className="bg-amber-50 text-amber-700 rounded-lg p-3 text-center text-sm">No QR scanned — points not credited</div>
                )}
              </div>
              <button onClick={resetToIdle} className="mt-6 text-white/70 hover:text-white text-sm">Returns to idle in 10 seconds...</button>
            </div>
          )}
        </div>

        {/* Right Panel */}
        {state === 'active' && (
          <div className="w-80 bg-white border-l flex flex-col shrink-0 min-h-0">
            {/* User Status */}
            <div className="p-4 border-b shrink-0">
              {linkedUser ? (
                <div className="flex items-center gap-2 text-green-700 bg-green-50 rounded-lg p-3">
                  <span className="text-lg">✓</span><span className="font-medium">{linkedUser}</span>
                </div>
              ) : (
                <div className="flex items-center gap-2 text-amber-700 bg-amber-50 rounded-lg p-3">
                  <span className="text-lg">📱</span><span className="text-sm">Scan QR code to earn points</span>
                </div>
              )}
            </div>

            {/* Input Method */}
            <div className="p-4 border-b shrink-0">
              <div className="text-xs font-medium text-gray-500 uppercase mb-2">Input Slot</div>
              <div className="grid grid-cols-2 gap-2">
                {inputMethods.map((m) => (
                  <button
                    key={m.key}
                    onClick={() => setInputMethod(m.key)}
                    className={`p-3 rounded-lg text-center text-sm font-medium transition ${
                      inputMethod === m.key ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    }`}
                  >
                    <div className="text-lg">{m.icon}</div>{m.label}
                  </button>
                ))}
              </div>
            </div>

            {/* Wash Basin Toggle — resets per item */}
            <div className="p-4 border-b shrink-0">
              <button
                onClick={cupRinsed ? () => setCupRinsed(false) : toggleRinse}
                className={`w-full p-3 rounded-lg text-sm font-medium transition ${
                  cupRinsed ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700 hover:bg-blue-50'
                }`}
              >
                {cupRinsed ? '💧 Cup Rinsed ✓ (tap to undo)' : '💧 Mark Cup as Rinsed'}
              </button>
            </div>

            {/* Detect Button */}
            <div className="p-4 border-b shrink-0">
              <button onClick={detectItem} disabled={detecting} className="w-full bg-teal-600 hover:bg-teal-700 text-white py-4 rounded-xl text-lg font-semibold disabled:opacity-50">
                {detecting ? 'Detecting...' : '📸 Capture & Detect'}
              </button>
            </div>

            {/* Detection History — scrollable */}
            <div className="flex-1 overflow-y-auto p-4 min-h-0">
              <div className="text-xs font-medium text-gray-500 uppercase mb-2">Items ({detections.length})</div>
              {detections.length === 0 ? (
                <p className="text-gray-400 text-sm">No items detected yet</p>
              ) : (
                <div className="space-y-2">
                  {detections.map((d, i) => (
                    <div key={d.id} className="bg-gray-50 rounded-lg p-3 text-sm">
                      <div className="flex justify-between items-start">
                        <div className="font-medium">#{detections.length - i} {d.waste_type.replace(/_/g, ' ')}</div>
                        <span className="text-xs text-gray-400">{new Date(d.created_at).toLocaleTimeString()}</span>
                      </div>
                      <div className="text-gray-500 flex justify-between mt-1">
                        <span>{d.input_method.replace(/_/g, ' ')}</span>
                        <span>{d.confidence}%</span>
                      </div>
                      {d.detected_brand && (
                        <div className="text-teal-600 text-xs mt-1 font-medium">{d.detected_brand}</div>
                      )}
                    </div>
                  ))}
                </div>
              )}
            </div>

            {/* End Session */}
            <div className="p-4 border-t shrink-0">
              <button onClick={endSession} className="w-full bg-red-500 hover:bg-red-600 text-white py-3 rounded-xl font-semibold">End Session</button>
            </div>
          </div>
        )}
      </main>
    </div>
  )
}
