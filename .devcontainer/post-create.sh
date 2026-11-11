#!/usr/bin/env bash
# Prepara o ambiente de desenvolvimento do MapeiaCEP:
#  - instala o Google Chrome (estável) para testes de navegador
#  - registra o MCP do Chrome DevTools no escopo do usuário do Claude Code
set -euo pipefail

CHROME_BIN=/usr/bin/google-chrome

if ! command -v google-chrome >/dev/null 2>&1; then
  echo ">> Instalando Google Chrome..."
  tmp_deb="$(mktemp -d)/google-chrome.deb"
  wget -q -O "$tmp_deb" https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
  sudo apt-get update -qq
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y -qq "$tmp_deb"
  rm -f "$tmp_deb"
fi
google-chrome --version

if command -v claude >/dev/null 2>&1; then
  if claude mcp get chrome-devtools >/dev/null 2>&1; then
    echo ">> MCP chrome-devtools já configurado."
  else
    echo ">> Registrando MCP chrome-devtools (escopo do usuário)..."
    claude mcp add chrome-devtools --scope user -- \
      npx -y chrome-devtools-mcp@latest \
      --headless --isolated \
      --executablePath="$CHROME_BIN" \
      --viewport=1280x800
  fi
else
  echo ">> Claude Code não encontrado; pulei o registro do MCP chrome-devtools."
  echo "   Depois de instalar o Claude Code, rode: bash .devcontainer/post-create.sh"
fi

if [ -f composer.json ]; then
  composer install --no-interaction
fi
