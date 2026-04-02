<?php

namespace App\Controllers;

use App\Models\CompatibilidadModel;
use App\Models\PiezaMaestraModel;
use App\Models\MotoModel;

class Compatibilidades extends BaseAdminController
{
    private CompatibilidadModel $model;
    private PiezaMaestraModel   $piezaModel;
    private MotoModel           $motoModel;

    public function __construct()
    {
        $this->model      = new CompatibilidadModel();
        $this->piezaModel = new PiezaMaestraModel();
        $this->motoModel  = new MotoModel();
    }

    // ── Listado ────────────────────────────────────────────────

    /**
     * GET /compatibilidades
     * Lista todas las relaciones pieza–moto con detalle completo.
     */
    public function index(): string
    {
        return view('compatibilidades/index', [
            'title'          => 'Compatibilidades — Catálogo',
            'pageTitle'      => 'Compatibilidades',
            'compatibilidades' => $this->model->getAllDetailed(),
        ]);
    }

    // ── Crear ──────────────────────────────────────────────────

    /**
     * GET /compatibilidades/create
     * Muestra el formulario para crear una nueva compatibilidad pieza–moto.
     */
    public function create(): string
    {
        return view('compatibilidades/_form', [
            'compat' => null,
            'piezas' => $this->piezaModel->orderBy('nombre')->findAll(),
            'motos'  => $this->motoModel->getAllWithMarca(),
            'errors' => [],
            'old'    => [],
        ]);
    }

    /**
     * POST /compatibilidades
     * Valida y persiste una nueva compatibilidad pieza–moto.
     * Rechaza el par si ya existe en la tabla (restricción de unicidad).
     */
    public function store()
    {
        $post = $this->request->getPost();

        if (!$this->validate([
            'pieza_maestra_id' => 'required|is_natural_no_zero',
            'motocicleta_id'   => 'required|is_natural_no_zero',
        ])) {
            return view('compatibilidades/_form', [
                'compat' => null,
                'piezas' => $this->piezaModel->orderBy('nombre')->findAll(),
                'motos'  => $this->motoModel->getAllWithMarca(),
                'errors' => $this->validator->getErrors(),
                'old'    => $post,
            ]);
        }

        // Verificar unicidad de la par (pieza, moto)
        $exists = $this->model
            ->where('pieza_maestra_id', (int) $post['pieza_maestra_id'])
            ->where('motocicleta_id',   (int) $post['motocicleta_id'])
            ->first();

        if ($exists) {
            return view('compatibilidades/_form', [
                'compat' => null,
                'piezas' => $this->piezaModel->orderBy('nombre')->findAll(),
                'motos'  => $this->motoModel->getAllWithMarca(),
                'errors' => ['general' => 'Esta combinación pieza–moto ya existe.'],
                'old'    => $post,
            ]);
        }

        $this->model->insert([
            'pieza_maestra_id'        => (int) $post['pieza_maestra_id'],
            'motocicleta_id'          => (int) $post['motocicleta_id'],
            'confirmada'              => 0,
            'contador_confirmaciones' => 0,
        ]);

        session()->setFlashdata('success', 'Compatibilidad creada correctamente.');
        return $this->htmxRedirect(site_url('/compatibilidades'));
    }

    // ── Editar ─────────────────────────────────────────────────

    /**
     * GET /compatibilidades/{id}/edit
     * Muestra el formulario precargado con los datos de la compatibilidad.
     *
     * @param int $id ID de la compatibilidad
     */
    public function edit(int $id): string
    {
        $compat = $this->model->find($id);

        return view('compatibilidades/_form', [
            'compat' => $compat,
            'piezas' => $this->piezaModel->orderBy('nombre')->findAll(),
            'motos'  => $this->motoModel->getAllWithMarca(),
            'errors' => [],
            'old'    => [],
        ]);
    }

    /**
     * PUT /compatibilidades/{id}
     * Valida y actualiza la relación pieza–moto.
     * Rechaza si el nuevo par ya existe en otro registro.
     *
     * @param int $id ID de la compatibilidad
     */
    public function update(int $id)
                'errors' => $this->validator->getErrors(),
                'old'    => $post,
            ]);
        }

        // Verificar unicidad excluyendo el registro actual
        $exists = $this->model
            ->where('pieza_maestra_id', (int) $post['pieza_maestra_id'])
            ->where('motocicleta_id',   (int) $post['motocicleta_id'])
            ->where('id !=',            $id)
            ->first();

        if ($exists) {
            return view('compatibilidades/_form', [
                'compat' => $this->model->find($id),
                'piezas' => $this->piezaModel->orderBy('nombre')->findAll(),
                'motos'  => $this->motoModel->getAllWithMarca(),
                'errors' => ['general' => 'Esta combinación pieza–moto ya existe.'],
                'old'    => $post,
            ]);
        }

        $this->model->update($id, [
            'pieza_maestra_id' => (int) $post['pieza_maestra_id'],
            'motocicleta_id'   => (int) $post['motocicleta_id'],
        ]);

        session()->setFlashdata('success', 'Compatibilidad actualizada correctamente.');
        return $this->htmxRedirect(site_url('/compatibilidades'));
    }

    // ── Eliminar ───────────────────────────────────────────────

    /**
     * DELETE /compatibilidades/{id}
     * Elimina la compatibilidad y devuelve el partial HTML actualizado (HTMX swap).
     *
     * @param int $id ID de la compatibilidad
     */
    public function delete(int $id)
    {
        $this->model->delete($id);

        return $this->response->setBody(
            view('compatibilidades/_rows', [
                'compatibilidades' => $this->model->getAllDetailed(),
            ])
        );
    }
}
