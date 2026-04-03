import { useState, useEffect, useRef } from 'react'
import { api } from './api'
import { useWebcam } from './hooks/useWebcam'
import { useQRScanner } from './hooks/useQRScanner'

type InputMethod = 'cup_slot' | 'lid_slot' | 'straw_slot' | 'general_intake'
type AppState = 'idle' | 'active' | 'summary'

interface Detection {
  id: number
  waste_type: string
  input_method: string
  confidence: number
  detected_brand: string | null
}

interface SessionSummary {
  items_count: number
  cup_rinsed: boolean
  points_earned: number
  user_linked: boolean
}

const SERIAL = import.meta.env.VITE_BIN_SERIAL || 'MBS-SB-001'

export default function App() {
  const { videoRef, canvasRef, isReady, error, captureFrame } = useWebcam()
  const { scanForQR } = useQRScanner()

  const [state, setState] = useState<AppState>('idle')
  const [sessionId, setSessionId] = useState<number | null>(null)
  const [binInfo, setBinInfo] = useState<any>(null)
  const [detections, setDetections] = useState<Detection[]>([])
  const [inputMethod, setInputMethod] = useState<InputMethod>('cup_slot')
  const [cupRinsed, setCupRinsed] = useState(false)
  const [linkedUser, setLinkedUser] = useState<string | null>(null)
  const [summary, setSummary] = useState<SessionSummary | null>(null)
  const [detecting, setDetecting] = useState(false)
  const [lastResult, setLastResult] = useState<string | null>(null)
  const qrIntervalRef = useRef<ReturnType<typeof setInterval> | null>(null)

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
          api.linkUser(SERIAL, sessionId, userId).then((res) => {
            if (res.user) {
              setLinkedUser(res.user.name)
            }
          })
        }
      }
    }, 1000)

    return () => {
      if (qrIntervalRef.current) clearInterval(qrIntervalRef.current)
    }
  }, [state, isReady, linkedUser, sessionId, scanForQR, videoRef, canvasRef])

  const startSession = async () => {
    const res = await api.startSession(SERIAL)
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
    if (!image) {
      setDetecting(false)
      return
    }

    const res = await api.detect(SERIAL, sessionId, image, inputMethod)

    if (res.detection) {
      setDetections((prev) => [...prev, res.detection])
      setLastResult(
        `${res.detection.waste_type} (${res.detection.confidence}%)${res.detection.detected_brand ? ` — ${res.detection.detected_brand}` : ''}`
      )
    } else {
      setLastResult('No item detected — try again')
    }

    setDetecting(false)
  }

  const toggleRinse = async () => {
    if (!sessionId) return
    await api.markRinsed(SERIAL, sessionId)
    setCupRinsed(true)
  }

  const endSession = async () => {
    if (!sessionId) return
    const res = await api.endSession(SERIAL, sessionId)
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

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <header className="bg-white border-b px-6 py-3 flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="w-8 h-8 bg-teal-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">
            M
          </div>
          <span className="font-semibold text-gray-900">Mobius Bin</span>
          <span className="text-sm text-gray-500">{SERIAL}</span>
        </div>
        <div className="flex items-center gap-4 text-sm">
          {binInfo && (
            <>
              <span className="text-gray-500">Fill: {binInfo.fill_level}%</span>
              <span className="text-gray-500">
                {binInfo.outlet} ({binInfo.brand})
              </span>
            </>
          )}
          <div className={`w-2 h-2 rounded-full ${isReady ? 'bg-green-500' : 'bg-red-500'}`} />
        </div>
      </header>

      <main className="flex-1 flex">
        <div className="flex-1 relative bg-black">
          <video ref={videoRef} autoPlay playsInline muted className="w-full h-full object-cover" />
          <canvas ref={canvasRef} className="hidden" />

          {error && (
            <div className="absolute inset-0 flex items-center justify-center bg-black/80 text-white text-lg">
              {error}
            </div>
          )}

          {lastResult && state === 'active' && (
            <div className="absolute top-4 left-4 right-4 bg-white/90 rounded-lg p-3 text-center font-medium">
              {lastResult}
            </div>
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
              <button
                onClick={startSession}
                disabled={!isReady}
                className="bg-teal-600 hover:bg-teal-700 text-white px-8 py-4 rounded-xl text-xl font-semibold disabled:opacity-50"
              >
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
                  <div>
                    <div className="text-2xl font-bold">{summary.items_count}</div>
                    <div className="text-gray-500 text-sm">items recycled</div>
                  </div>
                  <div>
                    <div className="text-2xl font-bold">{summary.cup_rinsed ? 'Yes' : 'No'}</div>
                    <div className="text-gray-500 text-sm">cup rinsed</div>
                  </div>
                </div>
                {summary.user_linked ? (
                  <div className="bg-teal-50 text-teal-700 rounded-lg p-3 text-center text-sm">
                    Points credited to your account
                  </div>
                ) : (
                  <div className="bg-amber-50 text-amber-700 rounded-lg p-3 text-center text-sm">
                    No QR scanned — points not credited
                  </div>
                )}
              </div>
              <button onClick={resetToIdle} className="mt-6 text-white/70 hover:text-white text-sm">
                Returns to idle in 10 seconds...
              </button>
            </div>
          )}
        </div>

        {state === 'active' && (
          <div className="w-80 bg-white border-l flex flex-col">
            <div className="p-4 border-b">
              {linkedUser ? (
                <div className="flex items-center gap-2 text-green-700 bg-green-50 rounded-lg p-3">
                  <span className="text-lg">✓</span>
                  <span className="font-medium">{linkedUser}</span>
                </div>
              ) : (
                <div className="flex items-center gap-2 text-amber-700 bg-amber-50 rounded-lg p-3">
                  <span className="text-lg">📱</span>
                  <span className="text-sm">Scan QR code to earn points</span>
                </div>
              )}
            </div>

            <div className="p-4 border-b">
              <div className="text-xs font-medium text-gray-500 uppercase mb-2">Input Slot</div>
              <div className="grid grid-cols-2 gap-2">
                {inputMethods.map((m) => (
                  <button
                    key={m.key}
                    onClick={() => setInputMethod(m.key)}
                    className={`p-3 rounded-lg text-center text-sm font-medium transition ${
                      inputMethod === m.key
                        ? 'bg-teal-600 text-white'
                        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                    }`}
                  >
                    <div className="text-lg">{m.icon}</div>
                    {m.label}
                  </button>
                ))}
              </div>
            </div>

            <div className="p-4 border-b">
              <button
                onClick={toggleRinse}
                disabled={cupRinsed}
                className={`w-full p-3 rounded-lg text-sm font-medium transition ${
                  cupRinsed
                    ? 'bg-blue-100 text-blue-700'
                    : 'bg-gray-100 text-gray-700 hover:bg-blue-50'
                }`}
              >
                {cupRinsed ? '💧 Cup Rinsed ✓' : '💧 Mark Cup as Rinsed'}
              </button>
            </div>

            <div className="p-4 border-b">
              <button
                onClick={detectItem}
                disabled={detecting}
                className="w-full bg-teal-600 hover:bg-teal-700 text-white py-4 rounded-xl text-lg font-semibold disabled:opacity-50"
              >
                {detecting ? 'Detecting...' : '📸 Capture & Detect'}
              </button>
            </div>

            <div className="flex-1 overflow-y-auto p-4">
              <div className="text-xs font-medium text-gray-500 uppercase mb-2">
                Items ({detections.length})
              </div>
              {detections.length === 0 ? (
                <p className="text-gray-400 text-sm">No items detected yet</p>
              ) : (
                <div className="space-y-2">
                  {detections.map((d, i) => (
                    <div key={i} className="bg-gray-50 rounded-lg p-3 text-sm">
                      <div className="font-medium">{d.waste_type.replace('_', ' ')}</div>
                      <div className="text-gray-500 flex justify-between">
                        <span>{d.input_method.replace('_', ' ')}</span>
                        <span>{d.confidence}%</span>
                      </div>
                      {d.detected_brand && (
                        <div className="text-teal-600 text-xs mt-1">{d.detected_brand}</div>
                      )}
                    </div>
                  ))}
                </div>
              )}
            </div>

            <div className="p-4 border-t">
              <button
                onClick={endSession}
                className="w-full bg-red-500 hover:bg-red-600 text-white py-3 rounded-xl font-semibold"
              >
                End Session
              </button>
            </div>
          </div>
        )}
      </main>
    </div>
  )
}
