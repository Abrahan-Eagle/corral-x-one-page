<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ExchangeRateController extends Controller
{
    /**
     * Obtener tasa de cambio USD a Bs del BCV automáticamente
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBcvRate()
    {
        // Cachear por 1 hora para evitar demasiadas llamadas al BCV
        $result = Cache::remember('bcv_usd_rate_data', 3600, function () {
            $rate = null;
            $source = 'unknown';
            
            // MÉTODO 1 (PRINCIPAL): API oficial de dolarapi.com - Tasa del BCV actualizada
            // Documentación: https://ve.dolarapi.com/docs/venezuela/operations/get-dolar-oficial.html
            try {
                $response = Http::timeout(8)->get('https://ve.dolarapi.com/v1/dolares/oficial');
                if ($response->successful()) {
                    $data = $response->json();
                    // Formato: {"fuente": "oficial", "nombre": "Oficial", "promedio": 247.3, ...}
                    if (isset($data['promedio']) && $data['promedio'] !== null) {
                        $tempRate = (float) $data['promedio'];
                        // Validar que la tasa sea válida (positiva y razonable)
                        if ($tempRate > 0 && $tempRate < 1000000) {
                            $rate = $tempRate;
                            $source = 'dolarapi.com (oficial BCV)';
                            \Log::info('Tasa BCV obtenida desde dolarapi.com: ' . $rate);
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::debug('dolarapi.com falló: ' . $e->getMessage());
            }
            
            // MÉTODO 2: Si el método 1 falló, intentar con bcvapi.tech
            if ($rate === null) {
                try {
                    $response = Http::timeout(8)->get('https://bcvapi.tech/api/v1/exchange-rate');
                    if ($response->successful()) {
                        $data = $response->json();
                        // Formato: {"usd": 247.40} o {"rate": 247.40} o {"data": {"usd": 247.40}}
                        if (isset($data['usd'])) {
                            $rate = (float) $data['usd'];
                            $source = 'bcvapi.tech';
                        } elseif (isset($data['rate'])) {
                            $rate = (float) $data['rate'];
                            $source = 'bcvapi.tech';
                        } elseif (isset($data['data']['usd'])) {
                            $rate = (float) $data['data']['usd'];
                            $source = 'bcvapi.tech';
                        }
                    }
                } catch (\Exception $e) {
                    \Log::debug('bcvapi.tech falló: ' . $e->getMessage());
                }
            }
            
            // MÉTODO 3: Si los métodos anteriores fallaron, intentar con pydolarve.org
            if ($rate === null) {
                try {
                    $response = Http::timeout(8)->get('https://api.pydolarve.org/api/v1/dollar/rate/bcv');
                    if ($response->successful()) {
                        $data = $response->json();
                        // Formato: {"moneda": "USD", "precio": 247.40} o {"rate": 247.40}
                        if (isset($data['precio'])) {
                            $rate = (float) $data['precio'];
                            $source = 'pydolarve.org';
                        } elseif (isset($data['rate'])) {
                            $rate = (float) $data['rate'];
                            $source = 'pydolarve.org';
                        } elseif (isset($data['data']['precio'])) {
                            $rate = (float) $data['data']['precio'];
                            $source = 'pydolarve.org';
                        }
                    }
                } catch (\Exception $e) {
                    \Log::debug('pydolarve.org falló: ' . $e->getMessage());
                }
            }
            
            // MÉTODO 4: Scraping del sitio oficial del BCV (último recurso antes del cache)
            if ($rate === null) {
                try {
                    $response = Http::timeout(10)
                        ->withHeaders([
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                        ])
                        ->get('https://www.bcv.org.ve/');
                    
                    if ($response->successful()) {
                        $html = $response->body();
                        
                        // Buscar el patrón del tipo de cambio USD en el HTML
                        // El BCV muestra: $ USD: 247,40710000 o similar
                        // Patrones comunes en el HTML del BCV
                        $patterns = [
                            '/USD[^>]*>[\s]*([\d,\.]+)/i',
                            '/\$[\s]*USD[^>]*>[\s]*([\d,\.]+)/i',
                            '/tipo[\s]*de[\s]*cambio[^>]*USD[^>]*>[\s]*([\d,\.]+)/i',
                            '/data-usd="([\d,\.]+)"/i',
                            '/id="dolar"[^>]*>[\s]*([\d,\.]+)/i',
                            '/<strong[^>]*>[\s]*\$[\s]*USD[^<]*<\/strong>[^<]*<strong[^>]*>([\d,\.]+)<\/strong>/i',
                        ];
                        
                        foreach ($patterns as $pattern) {
                            if (preg_match($pattern, $html, $matches)) {
                                $rateStr = str_replace(',', '', $matches[1]);
                                $rate = (float) $rateStr;
                                if ($rate > 0 && $rate < 1000000) { // Validación razonable
                                    $source = 'bcv.org.ve (scraping)';
                                    break;
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::debug('Scraping BCV falló: ' . $e->getMessage());
                }
            }
            
            // Si todos los métodos fallaron, usar el último valor cacheado exitosamente
            if ($rate === null || $rate <= 0) {
                $lastRate = Cache::get('bcv_usd_rate_last');
                if ($lastRate && $lastRate > 0) {
                    $rate = $lastRate;
                    $source = 'cached (último valor exitoso)';
                    \Log::warning('No se pudo obtener tasa BCV en tiempo real, usando último valor cacheado exitosamente: ' . $lastRate);
                } else {
                    // NO HAY VALOR HARDCODEADO - Si nunca se ha obtenido un valor, retornar null
                    // El frontend debe manejar este caso mostrando un mensaje al usuario
                    \Log::error('CRÍTICO: No se pudo obtener tasa BCV y no hay valor cacheado. El sistema requiere conexión al BCV.');
                    return null; // Retornar null para indicar que no hay tasa disponible
                }
            } else {
                // Guardar el valor exitoso para usar como fallback en el futuro (24 horas)
                Cache::put('bcv_usd_rate_last', $rate, 86400);
            }
            
            return [
                'rate' => $rate,
                'source' => $source,
                'timestamp' => now()->toIso8601String(),
            ];
        });
        
        // Si no se pudo obtener ninguna tasa (ni cacheada ni en tiempo real)
        if ($result === null) {
            return response()->json([
                'error' => 'No se pudo obtener la tasa de cambio del BCV. Por favor, verifique su conexión a internet e intente nuevamente.',
                'message' => 'El sistema requiere conexión al Banco Central de Venezuela para obtener la tasa de cambio actual.',
            ], 503); // Service Unavailable
        }
        
        return response()->json([
            'rate' => $result['rate'],
            'currency_from' => 'USD',
            'currency_to' => 'VES',
            'source' => $result['source'],
            'cached' => Cache::has('bcv_usd_rate_data'),
            'last_updated' => $result['timestamp'],
        ]);
    }
}
