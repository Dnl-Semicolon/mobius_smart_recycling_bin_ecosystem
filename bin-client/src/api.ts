const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1'

export const api = {
  async startSession(serial: string) {
    const res = await fetch(`${API_BASE}/bin/${serial}/sessions`, { method: 'POST' })
    return res.json()
  },

  async linkUser(serial: string, sessionId: number, userId: number) {
    const res = await fetch(`${API_BASE}/bin/${serial}/sessions/${sessionId}/link-user`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: userId }),
    })
    return res.json()
  },

  async detect(serial: string, sessionId: number, image: string, inputMethod: string) {
    const res = await fetch(`${API_BASE}/bin/${serial}/sessions/${sessionId}/detect`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ image, input_method: inputMethod }),
    })
    return res.json()
  },

  async endSession(serial: string, sessionId: number) {
    const res = await fetch(`${API_BASE}/bin/${serial}/sessions/${sessionId}/end`, { method: 'POST' })
    return res.json()
  },

  async markRinsed(serial: string, sessionId: number) {
    const res = await fetch(`${API_BASE}/bin/${serial}/sessions/${sessionId}/rinse`, { method: 'POST' })
    return res.json()
  },
}
