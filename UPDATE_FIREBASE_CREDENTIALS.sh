#!/bin/bash

# Script para actualizar credenciales de Firebase a corralx-777-aipp

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}🔧 Actualizando Credenciales de Firebase a corralx-777-aipp${NC}"
echo ""

cd /var/www/html/proyectos/AIPP-RENNY/DESARROLLO/CorralX/CorralX-Backend

# Verificar que existe el archivo de credenciales
echo -e "${YELLOW}📁 Buscando archivo de credenciales de corralx-777-aipp...${NC}"
CREDENTIALS_FILE=$(ls storage/app/corralx-777-aipp-firebase-adminsdk-*.json 2>/dev/null | head -1)

if [ -z "$CREDENTIALS_FILE" ]; then
    echo -e "${RED}❌ No se encontró archivo de credenciales de corralx-777-aipp${NC}"
    echo ""
    echo "Por favor, primero sube el archivo descargado de Firebase Console:"
    echo "  1. Descarga el archivo desde: https://console.firebase.google.com/project/corralx-777-aipp/settings/serviceaccounts/adminsdk"
    echo "  2. Cópialo a: storage/app/"
    echo ""
    echo "Luego ejecuta este script nuevamente."
    exit 1
fi

echo -e "${GREEN}✅ Archivo encontrado: $CREDENTIALS_FILE${NC}"

# Verificar Project ID
PROJECT_ID=$(php -r "\$json = json_decode(file_get_contents('$CREDENTIALS_FILE'), true); echo \$json['project_id'] ?? 'N/A';" 2>/dev/null)

if [ "$PROJECT_ID" != "corralx-777-aipp" ]; then
    echo -e "${RED}❌ Error: El archivo no pertenece al proyecto corralx-777-aipp${NC}"
    echo -e "${YELLOW}   Project ID encontrado: $PROJECT_ID${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Project ID correcto: $PROJECT_ID${NC}"

# Obtener nombre del archivo sin la ruta completa
FILENAME=$(basename "$CREDENTIALS_FILE")

echo ""
echo -e "${YELLOW}📝 Actualizando archivo .env...${NC}"

# Crear backup del .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
echo -e "${GREEN}✅ Backup creado: .env.backup.$(date +%Y%m%d_%H%M%S)${NC}"

# Actualizar FIREBASE_CREDENTIALS
sed -i "s|FIREBASE_CREDENTIALS=.*|FIREBASE_CREDENTIALS=storage/app/$FILENAME|g" .env

# Actualizar FIREBASE_DATABASE_URL
sed -i "s|FIREBASE_DATABASE_URL=.*|FIREBASE_DATABASE_URL=https://corralx-777-aipp-default-rtdb.firebaseio.com|g" .env

# Actualizar FIREBASE_STORAGE_BUCKET
sed -i "s|FIREBASE_STORAGE_BUCKET=.*|FIREBASE_STORAGE_BUCKET=corralx-777-aipp.firebasestorage.app|g" .env

echo -e "${GREEN}✅ .env actualizado${NC}"

# Limpiar caché
echo ""
echo -e "${YELLOW}🧹 Limpiando caché de Laravel...${NC}"
php artisan config:clear
php artisan cache:clear
echo -e "${GREEN}✅ Caché limpiado${NC}"

# Verificar configuración
echo ""
echo -e "${YELLOW}🔍 Verificando configuración...${NC}"
CONFIG_CREDENTIALS=$(php artisan tinker --execute="echo config('services.firebase.credentials');" 2>/dev/null | tail -1)

if [[ "$CONFIG_CREDENTIALS" == *"corralx-777-aipp"* ]]; then
    echo -e "${GREEN}✅ Configuración cargada correctamente${NC}"
    echo -e "${GREEN}   Path: $CONFIG_CREDENTIALS${NC}"
else
    echo -e "${RED}❌ Error: La configuración no se cargó correctamente${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✅ Actualización completada exitosamente${NC}"
echo ""
echo "📋 Próximos pasos:"
echo "  1. Los usuarios deben hacer re-login para generar nuevos tokens FCM"
echo "  2. Probar enviando un mensaje entre dos usuarios"
echo "  3. Verificar que las notificaciones push lleguen correctamente"
echo ""

