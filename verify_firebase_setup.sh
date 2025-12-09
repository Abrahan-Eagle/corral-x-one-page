#!/bin/bash

# Script para verificar la configuración de Firebase

echo "🔍 Verificando Configuración de Firebase"
echo "========================================"
echo ""

cd /var/www/html/proyectos/AIPP-RENNY/DESARROLLO/CorralX/CorralX-Backend

# Colores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 1. Verificar archivo de credenciales
echo "1️⃣ Verificando archivo de credenciales..."
CREDENTIALS_FILE=$(grep FIREBASE_CREDENTIALS .env | cut -d'=' -f2)

if [ -z "$CREDENTIALS_FILE" ]; then
    echo -e "${RED}❌ FIREBASE_CREDENTIALS no encontrado en .env${NC}"
    exit 1
fi

# Resolver ruta completa
if [[ $CREDENTIALS_FILE != /* ]]; then
    # Es ruta relativa
    CREDENTIALS_FILE="$(pwd)/$CREDENTIALS_FILE"
fi

if [ -f "$CREDENTIALS_FILE" ]; then
    echo -e "${GREEN}✅ Archivo encontrado: $CREDENTIALS_FILE${NC}"
else
    echo -e "${RED}❌ Archivo NO encontrado: $CREDENTIALS_FILE${NC}"
    exit 1
fi

# 2. Verificar Project ID
echo ""
echo "2️⃣ Verificando Project ID..."
PROJECT_ID=$(php -r "\$json = json_decode(file_get_contents('$CREDENTIALS_FILE'), true); echo \$json['project_id'] ?? 'N/A';" 2>/dev/null)
PROJECT_NUMBER=$(php -r "\$json = json_decode(file_get_contents('$CREDENTIALS_FILE'), true); echo \$json['project_number'] ?? 'N/A';" 2>/dev/null)

echo "   Project ID: $PROJECT_ID"
echo "   Project Number: $PROJECT_NUMBER"

if [ "$PROJECT_ID" == "corralx-777-aipp" ]; then
    echo -e "${GREEN}✅ Project ID correcto: corralx-777-aipp${NC}"
else
    echo -e "${RED}❌ Project ID incorrecto. Esperado: corralx-777-aipp, Encontrado: $PROJECT_ID${NC}"
fi

# 3. Verificar configuración en Laravel
echo ""
echo "3️⃣ Verificando configuración cargada en Laravel..."
CONFIG_PATH=$(php artisan tinker --execute="echo config('services.firebase.credentials');" 2>/dev/null | tail -1)

if [ -n "$CONFIG_PATH" ]; then
    echo -e "${GREEN}✅ Configuración cargada: $CONFIG_PATH${NC}"
else
    echo -e "${RED}❌ No se pudo cargar la configuración${NC}"
fi

# 4. Verificar Frontend para comparar
echo ""
echo "4️⃣ Comparando con Frontend..."
FRONTEND_PROJECT_ID=$(cat ../CorralX-Frontend/android/app/google-services.json 2>/dev/null | python3 -c "import json, sys; data=json.load(sys.stdin); print(data['project_info']['project_id'])" 2>/dev/null)
FRONTEND_PROJECT_NUMBER=$(cat ../CorralX-Frontend/android/app/google-services.json 2>/dev/null | python3 -c "import json, sys; data=json.load(sys.stdin); print(data['project_info']['project_number'])" 2>/dev/null)

if [ -n "$FRONTEND_PROJECT_ID" ]; then
    echo "   Frontend Project ID: $FRONTEND_PROJECT_ID"
    echo "   Frontend Project Number: $FRONTEND_PROJECT_NUMBER"
    
    if [ "$PROJECT_ID" == "$FRONTEND_PROJECT_ID" ]; then
        echo -e "${GREEN}✅ Project ID coincide entre frontend y backend${NC}"
    else
        echo -e "${RED}❌ Project ID NO coincide${NC}"
        echo -e "${YELLOW}   Frontend: $FRONTEND_PROJECT_ID${NC}"
        echo -e "${YELLOW}   Backend: $PROJECT_ID${NC}"
    fi
else
    echo -e "${YELLOW}⚠️ No se pudo leer google-services.json del frontend${NC}"
fi

echo ""
echo "========================================"
echo "✅ Verificación completada"
echo ""

