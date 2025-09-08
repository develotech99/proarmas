<?php

/**
 * Migración completa para el sistema de inventario de armería
 * Incluye todas las tablas necesarias con INTEGER para consistencia
 * Comando: php artisan make:migration crear_sistema_inventario_armeria_completo
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar las migraciones.
     */
    public function up(): void
    {
        // =========================================
        // TABLAS BASE
        // =========================================

        // Tabla de métodos de pago
        Schema::create('pro_metodos_pago', function (Blueprint $table) {
            $table->integer('metpago_id')->autoIncrement()->primary()->comment('ID método de pago');
            $table->string('metpago_descripcion', 50)->comment('efectivo, transferencia, etc.');
            $table->integer('metpago_situacion')->default(1)->comment('1 = activo, 0 = inactivo');
        });

        // Tabla de países
        Schema::create('pro_paises', function (Blueprint $table) {
            $table->integer('pais_id')->autoIncrement()->primary()->comment('ID de país');
            $table->string('pais_descripcion', 50)->nullable()->comment('Descripción del país');
            $table->integer('pais_situacion')->default(1)->comment('1 = activo, 0 = inactivo');
        });

        // Tabla de categorías
        Schema::create('pro_categorias', function (Blueprint $table) {
            $table->integer('categoria_id')->autoIncrement()->primary();
            $table->string('categoria_nombre', 100)->comment('ejemplo armas, accesorios, municiones, etc..');
            $table->integer('categoria_situacion')->default(1);
            
            // Índices
            $table->index('categoria_situacion');
        });

        // Tabla de subcategorías
        Schema::create('pro_subcategorias', function (Blueprint $table) {
            $table->integer('subcategoria_id')->autoIncrement()->primary();
            $table->string('subcategoria_nombre', 100)->comment('ejemplo pistola, fusil, chaleco, mira, etc....');
            $table->integer('subcategoria_idcategoria');
            $table->integer('subcategoria_situacion')->default(1);
            
            // Índices
            $table->index('subcategoria_idcategoria');
            $table->index('subcategoria_situacion');
            
            // Clave foránea
            $table->foreign('subcategoria_idcategoria')
                  ->references('categoria_id')
                  ->on('pro_categorias')
                  ->onDelete('restrict');
        });

        // Tabla de marcas
        Schema::create('pro_marcas', function (Blueprint $table) {
            $table->integer('marca_id')->autoIncrement()->primary()->comment('ID de marca');
            $table->string('marca_descripcion', 50)->nullable()->comment('system defense, glock, brigade');
            $table->integer('marca_situacion')->default(1)->comment('1 = activa, 0 = inactiva');
            
            // Índices
            $table->index('marca_situacion');
        });

        // Tabla de modelos
        Schema::create('pro_modelo', function (Blueprint $table) {
            $table->integer('modelo_id')->autoIncrement()->primary()->comment('ID de modelo');
            $table->string('modelo_descripcion', 50)->nullable()->comment('c9, bm-f-9, sd15');
            $table->integer('modelo_situacion')->default(1)->comment('1 = activo, 0 = inactivo');
            $table->integer('modelo_marca_id')->nullable();
            
            // Índices
            $table->index('modelo_marca_id');
            $table->index('modelo_situacion');
            
            // Clave foránea
            $table->foreign('modelo_marca_id')
                  ->references('marca_id')
                  ->on('pro_marcas')
                  ->onDelete('set null');
        });

        // Tabla de unidades de medida
        Schema::create('pro_unidades_medida', function (Blueprint $table) {
            $table->integer('unidad_id')->autoIncrement()->primary();
            $table->string('unidad_nombre', 50)->comment("Ej: 'milímetro', 'pulgada'");
            $table->string('unidad_abreviacion', 10)->comment("Ej: 'mm', 'in'");
            $table->string('unidad_tipo', 20)->default('longitud');
            $table->integer('unidad_situacion')->default(1)->comment('1 = activo, 0 = inactivo');
            
            // Índices
            $table->index('unidad_situacion');
        });

        // Tabla de calibres
        Schema::create('pro_calibres', function (Blueprint $table) {
            $table->integer('calibre_id')->autoIncrement()->primary();
            $table->string('calibre_nombre', 20)->comment("Ej: '9', '.45'");
            $table->integer('calibre_unidad_id');
            $table->decimal('calibre_equivalente_mm', 6, 2)->nullable();
            $table->integer('calibre_situacion')->default(1);
            
            // Índices
            $table->index('calibre_unidad_id');
            $table->index('calibre_situacion');
            
            // Clave foránea
            $table->foreign('calibre_unidad_id')
                  ->references('unidad_id')
                  ->on('pro_unidades_medida')
                  ->onDelete('restrict');
        });

        // =========================================
        // TABLAS DE INVENTARIO
        // =========================================

        // Tabla de productos
        Schema::create('pro_productos', function (Blueprint $table) {
            $table->integer('producto_id')->autoIncrement()->primary();
            $table->string('producto_nombre', 100);
            $table->string('producto_codigo_barra', 100)->unique()->nullable()->comment('si aplica');
            $table->integer('producto_categoria_id');
            $table->integer('producto_subcategoria_id');
            $table->integer('producto_marca_id');
            $table->integer('producto_modelo_id')->nullable()->comment('NULL si no aplica');
            $table->integer('producto_calibre_id')->nullable()->comment('NULL si no aplica');
            $table->boolean('producto_requiere_serie')->default(false);
            $table->boolean('producto_es_importado')->default(false)->comment('true = importación, false = compra local');
            $table->integer('producto_id_licencia')->nullable()->comment('Si viene de importación, se guarda aquí el ID de la licencia');
            $table->integer('producto_situacion')->default(1)->comment('1 = activo, 0 = inactivo');
            
            // Índices para optimizar consultas
            $table->index('producto_categoria_id');
            $table->index('producto_subcategoria_id');
            $table->index('producto_marca_id');
            $table->index('producto_modelo_id');
            $table->index('producto_calibre_id');
            $table->index('producto_situacion');
            $table->index(['producto_situacion', 'producto_categoria_id']);
            $table->index('producto_requiere_serie');
            
            // Claves foráneas
            $table->foreign('producto_categoria_id')
                  ->references('categoria_id')
                  ->on('pro_categorias')
                  ->onDelete('restrict');
                  
            $table->foreign('producto_subcategoria_id')
                  ->references('subcategoria_id')
                  ->on('pro_subcategorias')
                  ->onDelete('restrict');
                  
            $table->foreign('producto_marca_id')
                  ->references('marca_id')
                  ->on('pro_marcas')
                  ->onDelete('restrict');
                  
            $table->foreign('producto_modelo_id')
                  ->references('modelo_id')
                  ->on('pro_modelo')
                  ->onDelete('set null');
                  
            $table->foreign('producto_calibre_id')
                  ->references('calibre_id')
                  ->on('pro_calibres')
                  ->onDelete('set null');
        });

        // Tabla de fotos de productos (CORREGIDA - foreign key apunta a foto_producto_id)
        Schema::create('pro_productos_fotos', function (Blueprint $table) {
            $table->integer('foto_id')->autoIncrement()->primary();
            $table->integer('foto_producto_id');
            $table->string('foto_url', 255);
            $table->boolean('foto_principal')->default(false);
            $table->integer('foto_situacion')->default(1);
            
            // Índices
            $table->index('foto_producto_id');
            $table->index(['foto_producto_id', 'foto_situacion']);
            $table->index('foto_principal');
            
            // Clave foránea CORREGIDA
            $table->foreign('foto_producto_id')
                  ->references('producto_id')
                  ->on('pro_productos')
                  ->onDelete('cascade');
        });

        // Tabla de series de productos
        Schema::create('pro_series_productos', function (Blueprint $table) {
            $table->integer('serie_id')->autoIncrement()->primary();
            $table->integer('serie_producto_id');
            $table->string('serie_numero_serie', 200)->unique();
            $table->enum('serie_estado', ['disponible', 'reservado', 'vendido', 'baja'])->default('disponible');
            $table->timestamp('serie_fecha_ingreso')->useCurrent();
            $table->integer('serie_situacion')->default(1);
            
            // Índices para optimizar búsquedas
            $table->index('serie_producto_id');
            $table->index('serie_estado');
            $table->index(['serie_producto_id', 'serie_estado']);
            $table->index(['serie_estado', 'serie_situacion']);
            $table->index('serie_numero_serie');
            $table->index('serie_fecha_ingreso');
            
            // Clave foránea
            $table->foreign('serie_producto_id')
                  ->references('producto_id')
                  ->on('pro_productos')
                  ->onDelete('cascade');
        });

        // Tabla de lotes
        Schema::create('pro_lotes', function (Blueprint $table) {
            $table->integer('lote_id')->autoIncrement()->primary();
            $table->string('lote_codigo', 100)->unique()->comment("Ej: 'L2025-08-GLOCK-001'");
            $table->timestamp('lote_fecha')->useCurrent();
            $table->string('lote_descripcion', 255)->nullable();
            $table->integer('lote_situacion')->default(1);
            
            // Índices
            $table->index('lote_situacion');
            $table->index('lote_fecha');
            $table->index('lote_codigo');
        });

        Schema::create('pro_clientes', function (Blueprint $table) {
            $table->id('cliente_id');
            $table->enum('tipo', ['empresa','persona']);
            $table->string('nombre_empresa', 200)->nullable();
            $table->string('nombre', 200)->comment('NOMBRE DEL DUENO DE LA EMPRESA O PERSONA INDIVIDUAL');
            $table->string('razon_social', 200)->nullable()->comment('solo para empresas');
            $table->string('ubicacion', 100)->nullable();
            $table->integer('situacion')->default(1);
            $table->timestamps();
        });
        // Tabla de movimientos de inventario (CORREGIDA - mov_tipo con tipo de dato)
        Schema::create('pro_movimientos', function (Blueprint $table) {
            $table->integer('mov_id')->autoIncrement()->primary();
            $table->integer('mov_producto_id');
            $table->string('mov_tipo', 50)->comment('ingreso, egreso, baja, importación, ajuste');
            $table->string('mov_origen', 100)->nullable()->comment('importación, ajuste, venta, compra local, etc.');
            $table->integer('mov_cantidad');
            $table->timestamp('mov_fecha')->useCurrent();
            $table->integer('mov_usuario_id');
            $table->integer('mov_lote_id')->nullable()->comment('NULL si no aplica');
            $table->string('mov_observaciones', 250)->nullable();
            $table->integer('mov_situacion')->default(1);
            
            // Índices para reportes y consultas frecuentes
            $table->index('mov_producto_id');
            $table->index('mov_tipo');
            $table->index('mov_fecha');
            $table->index('mov_usuario_id');
            $table->index('mov_lote_id');
            $table->index('mov_situacion');
            $table->index(['mov_producto_id', 'mov_tipo']);
            $table->index(['mov_fecha', 'mov_tipo']);
            $table->index(['mov_producto_id', 'mov_fecha']);
            $table->index(['mov_situacion', 'mov_tipo']);
            $table->index(['mov_fecha', 'mov_situacion']);
            
            // Claves foráneas
            $table->foreign('mov_producto_id')
                  ->references('producto_id')
                  ->on('pro_productos')
                  ->onDelete('cascade');
                  
            $table->foreign('mov_lote_id')
                  ->references('lote_id')
                  ->on('pro_lotes')
                  ->onDelete('set null');
        });
    }

    /**
     * Reversar las migraciones.
     */
    public function down(): void
    {
        // Eliminar en orden inverso para respetar las claves foráneas
        Schema::dropIfExists('pro_movimientos');
        Schema::dropIfExists('pro_lotes');
        Schema::dropIfExists('pro_series_productos');
        Schema::dropIfExists('pro_productos_fotos');
        Schema::dropIfExists('pro_productos');
        Schema::dropIfExists('pro_calibres');
        Schema::dropIfExists('pro_unidades_medida');
        Schema::dropIfExists('pro_modelo');
        Schema::dropIfExists('pro_marcas');
        Schema::dropIfExists('pro_subcategorias');
        Schema::dropIfExists('pro_categorias');
        Schema::dropIfExists('pro_paises');
        Schema::dropIfExists('pro_metodos_pago');
        Schema::dropIfExists('pro_clientes');

    }
};

/**
 * MIGRACIÓN ALTERNATIVA - SOLUCIÓN 2: Crear claves foráneas por separado
 * 
 * Si aún tienes problemas, crea una segunda migración solo para las claves foráneas:
 * 
 * php artisan make:migration agregar_claves_foraneas_inventario
 */

/*
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pro_productos', function (Blueprint $table) {
            // Primero verifica que las tablas existan y sus tipos de datos
            $table->foreign('producto_categoria_id')->references('categoria_id')->on('pro_categorias');
            $table->foreign('producto_subcategoria_id')->references('subcategoria_id')->on('pro_subcategorias');
            $table->foreign('producto_marca_id')->references('marca_id')->on('pro_marcas');
            $table->foreign('producto_modelo_id')->references('modelo_id')->on('pro_modelo');
            $table->foreign('producto_calibre_id')->references('calibre_id')->on('pro_calibres');
            $table->foreign('producto_id_licencia')->references('lipaimp_id')->on('pro_licencias_para_importacion');
        });
    }

    public function down(): void
    {
        Schema::table('pro_productos', function (Blueprint $table) {
            $table->dropForeign(['producto_categoria_id']);
            $table->dropForeign(['producto_subcategoria_id']);
            $table->dropForeign(['producto_marca_id']);
            $table->dropForeign(['producto_modelo_id']);
            $table->dropForeign(['producto_calibre_id']);
            $table->dropForeign(['producto_id_licencia']);
        });
    }
};
*/

/**
 * Seeder para datos iniciales de prueba
 * Comando: php artisan make:seeder InventarioSeeder
 */

/*
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        // Datos de ejemplo para categorías (si no existen)
        if (!DB::table('categorias')->exists()) {
            DB::table('categorias')->insert([
                ['id' => 1, 'nombre' => 'Armas de Fuego', 'situacion' => 1],
                ['id' => 2, 'nombre' => 'Municiones', 'situacion' => 1],
                ['id' => 3, 'nombre' => 'Accesorios', 'situacion' => 1],
                ['id' => 4, 'nombre' => 'Equipos de Protección', 'situacion' => 1],
            ]);
        }

        // Datos de ejemplo para marcas (si no existen)
        if (!DB::table('marcas')->exists()) {
            DB::table('marcas')->insert([
                ['id' => 1, 'nombre' => 'Glock', 'situacion' => 1],
                ['id' => 2, 'nombre' => 'Smith & Wesson', 'situacion' => 1],
                ['id' => 3, 'nombre' => 'Sig Sauer', 'situacion' => 1],
                ['id' => 4, 'nombre' => 'Beretta', 'situacion' => 1],
                ['id' => 5, 'nombre' => 'Remington', 'situacion' => 1],
            ]);
        }

        // Datos de ejemplo para modelos (si no existen)
        if (!DB::table('modelos')->exists()) {
            DB::table('modelos')->insert([
                ['id' => 1, 'nombre' => 'G17', 'situacion' => 1],
                ['id' => 2, 'nombre' => 'G19', 'situacion' => 1],
                ['id' => 3, 'nombre' => 'M&P Shield', 'situacion' => 1],
                ['id' => 4, 'nombre' => 'P320', 'situacion' => 1],
                ['id' => 5, 'nombre' => '92FS', 'situacion' => 1],
            ]);
        }

        // Datos de ejemplo para calibres (si no existen)
        if (!DB::table('calibres')->exists()) {
            DB::table('calibres')->insert([
                ['id' => 1, 'nombre' => '9mm', 'situacion' => 1],
                ['id' => 2, 'nombre' => '.40 S&W', 'situacion' => 1],
                ['id' => 3, 'nombre' => '.45 ACP', 'situacion' => 1],
                ['id' => 4, 'nombre' => '.22 LR', 'situacion' => 1],
                ['id' => 5, 'nombre' => '.380 ACP', 'situacion' => 1],
            ]);
        }

        // Productos de ejemplo
        $productos = [
            [
                'producto_nombre' => 'Pistola Glock 17 Gen 5',
                'producto_codigo_barra' => '764503017291',
                'producto_categoria_id' => 1,
                'producto_subcategoria_id' => 1,
                'producto_marca_id' => 1,
                'producto_modelo_id' => 1,
                'producto_calibre_id' => 1,
                'producto_requiere_serie' => true,
                'producto_es_importado' => true,
                'producto_situacion' => 1
            ],
            [
                'producto_nombre' => 'Munición 9mm FMJ',
                'producto_codigo_barra' => '047700415505',
                'producto_categoria_id' => 2,
                'producto_subcategoria_id' => 2,
                'producto_marca_id' => 5,
                'producto_modelo_id' => null,
                'producto_calibre_id' => 1,
                'producto_requiere_serie' => false,
                'producto_es_importado' => false,
                'producto_situacion' => 1
            ],
            [
                'producto_nombre' => 'Pistola Smith & Wesson M&P Shield',
                'producto_codigo_barra' => '022188149531',
                'producto_categoria_id' => 1,
                'producto_subcategoria_id' => 1,
                'producto_marca_id' => 2,
                'producto_modelo_id' => 3,
                'producto_calibre_id' => 1,
                'producto_requiere_serie' => true,
                'producto_es_importado' => true,
                'producto_situacion' => 1
            ]
        ];

        foreach ($productos as $producto) {
            $productoId = DB::table('pro_productos')->insertGetId($producto);

            // Si requiere serie, crear algunas series de ejemplo
            if ($producto['producto_requiere_serie']) {
                $series = [];
                for ($i = 1; $i <= 5; $i++) {
                    $series[] = [
                        'serie_producto_id' => $productoId,
                        'serie_numero_serie' => strtoupper(substr($producto['producto_nombre'], 0, 3)) . sprintf('%06d', $productoId * 1000 + $i),
                        'serie_estado' => 'disponible',
                        'serie_fecha_ingreso' => Carbon::now()->subDays(rand(1, 30)),
                        'serie_situacion' => 1
                    ];
                }
                DB::table('pro_series_productos')->insert($series);

                // Crear movimiento de ingreso por series
                DB::table('pro_movimientos')->insert([
                    'mov_producto_id' => $productoId,
                    'mov_tipo' => 'ingreso',
                    'mov_origen' => 'Inventario Inicial',
                    'mov_cantidad' => 5,
                    'mov_fecha' => Carbon::now()->subDays(rand(1, 30)),
                    'mov_usuario_id' => 1, // Ajustar según tu tabla de usuarios
                    'mov_observaciones' => 'Carga inicial de inventario',
                    'mov_situacion' => 1
                ]);
            } else {
                // Crear lote para productos sin serie
                $loteId = DB::table('pro_lotes')->insertGetId([
                    'lote_codigo' => 'LOTE-' . date('Ymd') . '-' . $productoId,
                    'lote_fecha' => Carbon::now()->subDays(rand(1, 30)),
                    'lote_descripcion' => 'Lote inicial para ' . $producto['producto_nombre'],
                    'lote_situacion' => 1
                ]);

                // Crear movimiento de ingreso por cantidad
                DB::table('pro_movimientos')->insert([
                    'mov_producto_id' => $productoId,
                    'mov_tipo' => 'ingreso',
                    'mov_origen' => 'Inventario Inicial',
                    'mov_cantidad' => 1000, // 1000 unidades de munición
                    'mov_fecha' => Carbon::now()->subDays(rand(1, 30)),
                    'mov_usuario_id' => 1,
                    'mov_lote_id' => $loteId,
                    'mov_observaciones' => 'Carga inicial de inventario',
                    'mov_situacion' => 1
                ]);
            }
        }

        echo "Datos de ejemplo creados correctamente\n";
    }
}
*/