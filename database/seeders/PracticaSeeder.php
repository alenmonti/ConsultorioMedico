<?php

namespace Database\Seeders;

use App\Models\Practica;
use Illuminate\Database\Seeder;

class PracticaSeeder extends Seeder
{
    public function run(): void
    {
        $medicoId = 5; // Maillin medico id

        $practicas = [
            // INYECTABLES
            ['nombre' => 'Rostro - Plasma',                                     'descripcion' => 'Plasma',                                          'costo' => 80000,  'codigo_osde' => null,              'tipo' => 'INYECTABLES', 'duracion_min' => 40],
            ['nombre' => 'Más Cuello y Escote - Plasma',                        'descripcion' => 'Plasma',                                          'costo' => 100000, 'codigo_osde' => null,              'tipo' => 'INYECTABLES', 'duracion_min' => 40],
            ['nombre' => 'Capilar - Plasma',                                    'descripcion' => 'Plasma',                                          'costo' => 70000,  'codigo_osde' => null,              'tipo' => 'INYECTABLES', 'duracion_min' => 20],
            ['nombre' => 'Tercio Superior - Toxina Botulínica',                 'descripcion' => 'Toxina Botulínica',                               'costo' => 330000, 'codigo_osde' => null,              'tipo' => 'INYECTABLES', 'duracion_min' => 60],
            ['nombre' => 'Tercio Inferior - Toxina Botulínica',                 'descripcion' => 'Toxina Botulínica',                               'costo' => 330000, 'codigo_osde' => null,              'tipo' => 'INYECTABLES', 'duracion_min' => 60],
            ['nombre' => 'Full Face - Toxina Botulínica',                       'descripcion' => 'Toxina Botulínica',                               'costo' => 500000, 'codigo_osde' => null,              'tipo' => 'INYECTABLES', 'duracion_min' => 60],
            ['nombre' => 'Bruxismo - Toxina Botulínica',                        'descripcion' => 'Toxina Botulínica',                               'costo' => 300000, 'codigo_osde' => null,              'tipo' => 'INYECTABLES', 'duracion_min' => 60],
            ['nombre' => 'Hiperhidrosis Palmar, Plantar o Axilar - Toxina Botulínica', 'descripcion' => 'Toxina Botulínica',                        'costo' => 400000, 'codigo_osde' => null,              'tipo' => 'INYECTABLES', 'duracion_min' => 60],
            ['nombre' => 'Skinbooster Skin Vive',                               'descripcion' => 'Skinbooster',                                     'costo' => 350000, 'codigo_osde' => null,              'tipo' => 'INYECTABLES', 'duracion_min' => 60],
            ['nombre' => 'Harmonyca - Tensado y Contorno Facial',               'descripcion' => 'Ác. Hialurónico + Hidroxiapatita de Calcio',      'costo' => 550000, 'codigo_osde' => null,              'tipo' => 'INYECTABLES', 'duracion_min' => 60],
            ['nombre' => 'Modelado de Labios - Ác. Hialurónico Juvederm',      'descripcion' => 'Ácido Hialurónico Juvederm',                      'costo' => 350000, 'codigo_osde' => null,              'tipo' => 'INYECTABLES', 'duracion_min' => 60],

            // CLINICA
            ['nombre' => 'Consulta',                                            'descripcion' => null,                                              'costo' => 35000,  'codigo_osde' => '420101',          'tipo' => 'CLINICA',     'duracion_min' => 20],
            ['nombre' => 'Electrocoagulación',                                  'descripcion' => null,                                              'costo' => 45000,  'codigo_osde' => '130185',          'tipo' => 'CLINICA',     'duracion_min' => 20],
            ['nombre' => 'Topicación TCA',                                      'descripcion' => null,                                              'costo' => 35000,  'codigo_osde' => '130183',          'tipo' => 'CLINICA',     'duracion_min' => 20],
            ['nombre' => 'Infiltración',                                        'descripcion' => null,                                              'costo' => 35000,  'codigo_osde' => '130184',          'tipo' => 'CLINICA',     'duracion_min' => 20],
            ['nombre' => 'Biopsia de Piel',                                     'descripcion' => null,                                              'costo' => 80000,  'codigo_osde' => '130185',          'tipo' => 'CLINICA',     'duracion_min' => 40],
            ['nombre' => 'Cirugía Menor con o sin Biopsia',                     'descripcion' => null,                                              'costo' => 100000, 'codigo_osde' => '130186',          'tipo' => 'CLINICA',     'duracion_min' => 60],
            ['nombre' => 'Hiperhidrosis - Toxina Botulínica',                   'descripcion' => null,                                              'costo' => 0,      'codigo_osde' => '130191 + 923147', 'tipo' => 'CLINICA',     'duracion_min' => 60],

            // TOPICOS - Peeling
            ['nombre' => 'Rostro - Peeling Despigmentante',                     'descripcion' => 'Peeling Español',                                 'costo' => 60000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Rostro - Peeling Acné',                               'descripcion' => 'Peeling Español',                                 'costo' => 60000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Rostro - Peeling Envejecimiento',                     'descripcion' => 'Peeling Español',                                 'costo' => 60000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Cuello y Escote - Peeling Despigmentante',            'descripcion' => 'Peeling Español',                                 'costo' => 60000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Cuello y Escote - Peeling Envejecimiento',            'descripcion' => 'Peeling Español',                                 'costo' => 60000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Ojeras - Peeling Despigmentante',                     'descripcion' => 'Peeling Español',                                 'costo' => 50000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Ojeras - Peeling Envejecimiento',                     'descripcion' => 'Peeling Español',                                 'costo' => 50000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Rostro + Periocular - Peeling',                       'descripcion' => 'Peeling Español',                                 'costo' => 80000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Espalda - Peeling Acné',                              'descripcion' => 'Peeling Español',                                 'costo' => 60000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Glúteos - Peeling Acné',                              'descripcion' => 'Peeling Español',                                 'costo' => 60000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Rostro + Cuello y Escote - Peeling',                  'descripcion' => 'Peeling Español',                                 'costo' => 90000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Peeling Retinoico',                                   'descripcion' => null,                                              'costo' => 60000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Zona Periocular Premium (Global Eyecon)',              'descripcion' => null,                                              'costo' => 100000, 'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 40],
            ['nombre' => 'Peeling + Mesoterapia',                               'descripcion' => null,                                              'costo' => 80000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],

            // TOPICOS - Mesoterapia
            ['nombre' => 'Rostro - Mesoterapia Despigmentante',                 'descripcion' => 'Mesoterapia Nacional',                            'costo' => 35000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Rostro - Mesoterapia Acné',                           'descripcion' => 'Mesoterapia Nacional',                            'costo' => 35000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Rostro - Mesoterapia Envejecimiento',                 'descripcion' => 'Mesoterapia Nacional',                            'costo' => 35000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Cuello y Escote - Mesoterapia Despigmentante',        'descripcion' => 'Mesoterapia Nacional',                            'costo' => 35000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Cuello y Escote - Mesoterapia Acné',                  'descripcion' => 'Mesoterapia Nacional',                            'costo' => 35000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Cuello y Escote - Mesoterapia Envejecimiento',        'descripcion' => 'Mesoterapia Nacional',                            'costo' => 35000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Rostro + Cuello y Escote - Mesoterapia Despigmentante', 'descripcion' => 'Mesoterapia Nacional',                          'costo' => 50000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Rostro + Cuello y Escote - Mesoterapia Acné',         'descripcion' => 'Mesoterapia Nacional',                            'costo' => 50000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Rostro + Cuello y Escote - Mesoterapia Envejecimiento', 'descripcion' => 'Mesoterapia Nacional',                          'costo' => 50000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Mesoterapia Capilar',                                 'descripcion' => null,                                              'costo' => 40000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 20],

            // TOPICOS - Celulitis
            ['nombre' => 'Celulitis - Abdomen',                                 'descripcion' => null,                                              'costo' => 40000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 40],
            ['nombre' => 'Celulitis - Glúteos',                                 'descripcion' => null,                                              'costo' => 40000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 40],
            ['nombre' => 'Celulitis - Muslos',                                  'descripcion' => null,                                              'costo' => 40000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 40],
            ['nombre' => 'Celulitis Dos Zonas',                                 'descripcion' => null,                                              'costo' => 65000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 40],

            // TOPICOS - Microneedling / Dermapen
            ['nombre' => 'Rostro - Microneedling Despigmentante',               'descripcion' => 'Microneedling / Dermapen',                        'costo' => 70000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Rostro - Microneedling Acné',                         'descripcion' => 'Microneedling / Dermapen',                        'costo' => 70000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Rostro - Microneedling Envejecimiento',               'descripcion' => 'Microneedling / Dermapen',                        'costo' => 70000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Cuello y Escote - Microneedling Despigmentante',      'descripcion' => 'Microneedling / Dermapen',                        'costo' => 70000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Cuello y Escote - Microneedling Acné',                'descripcion' => 'Microneedling / Dermapen',                        'costo' => 70000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Cuello y Escote - Microneedling Envejecimiento',      'descripcion' => 'Microneedling / Dermapen',                        'costo' => 70000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Rostro + Cuello y Escote - Microneedling Despigmentante', 'descripcion' => 'Microneedling / Dermapen',                    'costo' => 90000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Rostro + Cuello y Escote - Microneedling Acné',       'descripcion' => 'Microneedling / Dermapen',                        'costo' => 90000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],

            // TOPICOS - Estrías y Reducción
            ['nombre' => 'Estrías - Por Zona',                                  'descripcion' => null,                                              'costo' => 50000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Estrías - Dos Zonas',                                 'descripcion' => null,                                              'costo' => 80000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 60],
            ['nombre' => 'Papada - Reducción Adiposidad',                       'descripcion' => null,                                              'costo' => 50000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 40],
            ['nombre' => 'Brazos - Reducción Adiposidad',                       'descripcion' => null,                                              'costo' => 80000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 40],
            ['nombre' => 'Muslos (Pantalón de Montar) - Reducción Adiposidad',  'descripcion' => null,                                              'costo' => 80000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 40],
            ['nombre' => 'Abdomen - Reducción Adiposidad',                      'descripcion' => null,                                              'costo' => 80000,  'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 40],
            ['nombre' => 'Dos Zonas - Reducción Adiposidad',                    'descripcion' => null,                                              'costo' => 100000, 'codigo_osde' => null,              'tipo' => 'TOPICOS',     'duracion_min' => 40],
        ];

        foreach ($practicas as $practica) {
            Practica::firstOrCreate(
                ['medico_id' => $medicoId, 'nombre' => $practica['nombre']],
                array_merge($practica, ['medico_id' => $medicoId])
            );
        }
    }
}
