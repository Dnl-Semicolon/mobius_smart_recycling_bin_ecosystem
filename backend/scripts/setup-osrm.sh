#!/bin/bash
# Setup OSRM with Malaysia OpenStreetMap data
# Run this once before starting Docker containers
set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
OSRM_DIR="$SCRIPT_DIR/../docker/osrm"
OSM_FILE="malaysia-singapore-brunei-latest.osm.pbf"
OSRM_FILE="malaysia-singapore-brunei-latest.osrm"

mkdir -p "$OSRM_DIR"
cd "$OSRM_DIR"

# Download Malaysia OSM extract if not present
if [ ! -f "$OSM_FILE" ]; then
    echo "Downloading Malaysia OSM data from Geofabrik (~200MB)..."
    curl -L -o "$OSM_FILE" "https://download.geofabrik.de/asia/malaysia-singapore-brunei-latest.osm.pbf"
    echo "Download complete."
else
    echo "OSM file already exists, skipping download."
fi

# Process OSM data for OSRM if not already done
if [ ! -f "$OSRM_FILE" ]; then
    echo "Extracting road network..."
    docker run -t -v "$OSRM_DIR:/data" osrm/osrm-backend osrm-extract -p /opt/car.lua "/data/$OSM_FILE"

    echo "Partitioning graph..."
    docker run -t -v "$OSRM_DIR:/data" osrm/osrm-backend osrm-partition "/data/$OSRM_FILE"

    echo "Customizing graph..."
    docker run -t -v "$OSRM_DIR:/data" osrm/osrm-backend osrm-customize "/data/$OSRM_FILE"

    echo "OSRM setup complete!"
else
    echo "OSRM files already exist, skipping processing."
fi

echo ""
echo "Ready! Start the containers with:"
echo "  docker compose -f docker-compose.override.yml up -d"
