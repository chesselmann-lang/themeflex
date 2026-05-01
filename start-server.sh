#!/bin/bash
# =============================================
# Starter Flavor — Local Dev Server + Tunnel
# Run: bash start-server.sh
# =============================================

PORT=8080
DIR="$(cd "$(dirname "$0")" && pwd)"

echo "🚀 Starting Starter Flavor Server..."
echo "📁 Serving: $DIR"
echo ""

# Kill any existing servers on this port
lsof -ti:$PORT | xargs kill -9 2>/dev/null || true
pkill -f localtunnel 2>/dev/null || true
sleep 1

# Start local server
npx serve "$DIR" -p $PORT --no-clipboard &
SERVER_PID=$!
echo "✅ Local server running: http://localhost:$PORT"
sleep 2

# Start tunnel
echo "🌐 Creating public tunnel..."
npx localtunnel --port $PORT > /tmp/sf_tunnel.txt 2>&1 &
TUNNEL_PID=$!
sleep 8

# Show URL
TUNNEL_URL=$(grep -o 'https://[^ ]*' /tmp/sf_tunnel.txt | head -1)
echo ""
echo "=============================================="
echo "🎉 STARTER FLAVOR IS ONLINE!"
echo "=============================================="
echo ""
echo "📍 Public URLs:"
echo "   Hub:      $TUNNEL_URL"
echo "   Showcase: $TUNNEL_URL/starter-flavor-showcase"
echo "   Docs:     $TUNNEL_URL/starter-flavor-docs"
echo "   Product:  $TUNNEL_URL/starter-flavor-product"
echo "   Demo:     $TUNNEL_URL/starter-flavor-demo"
echo "   Analysis: $TUNNEL_URL/elementra-demo"
echo ""
echo "📦 Downloads:"
echo "   Theme:    $TUNNEL_URL/downloads/starter-flavor-v2.zip"
echo "   Addons:   $TUNNEL_URL/downloads/starter-flavor-addons-v2.zip"
echo ""
echo "💡 Local: http://localhost:$PORT"
echo ""
echo "Press Ctrl+C to stop the server."
echo "=============================================="

# Keep running
wait $SERVER_PID
