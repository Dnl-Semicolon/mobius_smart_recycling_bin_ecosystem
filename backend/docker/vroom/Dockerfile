FROM ubuntu:24.04 AS builder

RUN apt-get update && apt-get install -y \
    git \
    build-essential \
    g++-14 \
    cmake \
    libssl-dev \
    libasio-dev \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

ENV CXX=g++-14

WORKDIR /opt
RUN git clone --recurse-submodules --depth 1 https://github.com/VROOM-Project/vroom.git && \
    cd vroom/src && \
    make -j$(nproc)

# --- Runtime stage: same base OS so glibc matches ---
FROM ubuntu:24.04

RUN apt-get update && apt-get install -y \
    curl \
    ca-certificates \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs git \
    && rm -rf /var/lib/apt/lists/*

# Copy built vroom binary
COPY --from=builder /opt/vroom/bin/vroom /usr/local/bin/vroom

# Install vroom-express
WORKDIR /opt/vroom-express
RUN git clone --depth 1 https://github.com/VROOM-Project/vroom-express.git . && \
    npm install

# Copy config
COPY config.yml /opt/vroom-express/config.yml

EXPOSE 3000
CMD ["npm", "start"]
