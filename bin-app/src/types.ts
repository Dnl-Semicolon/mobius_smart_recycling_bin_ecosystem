export interface ClassifyResult {
  waste_type: string;
  confidence: number;
  brand: string;
  detector: string;
}

export interface DetectionResponse {
  data: {
    id: number;
    bin_id: number;
    user_id: number | null;
    waste_type: string;
    confidence: number;
    detected_at: string;
  };
  message: string;
}

export interface BinResolveResponse {
  data: {
    id: number;
    serial_number: string;
    name: string;
  };
}

export interface HistoryItem {
  waste_type: string;
  confidence: number;
  brand: string;
  userId: number | null;
  timestamp: Date;
  detectionId: number;
}
