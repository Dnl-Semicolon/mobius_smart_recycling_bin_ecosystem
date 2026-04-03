import { useCallback } from 'react'
import jsQR from 'jsqr'

export function useQRScanner() {
  const scanForQR = useCallback(
    (video: HTMLVideoElement, canvas: HTMLCanvasElement): string | null => {
      if (!video.videoWidth) return null

      canvas.width = video.videoWidth
      canvas.height = video.videoHeight
      const ctx = canvas.getContext('2d')
      if (!ctx) return null

      ctx.drawImage(video, 0, 0)
      const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height)
      const code = jsQR(imageData.data, imageData.width, imageData.height)

      return code?.data || null
    },
    []
  )

  return { scanForQR }
}
