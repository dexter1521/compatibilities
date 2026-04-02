<?php

namespace App\Controllers;

use App\Models\PiezaMaestraModel;

class Piezas extends BaseAdminController
{
    private PiezaMaestraModel $model;

    public function __construct()
    {
        $this->model = new PiezaMaestraModel();
    }

    // ── Listado ────────────────────────────────────────────────

    /**
     * GET /piezas
     * Lista todas las piezas maestras ordenadas por nombre.
     */
    public function index(): string
    {
        return view('piezas/index', [
            'title'     => 'Piezas Maestras — Compatibilidades',
            'pageTitle' => 'Piezas Maestras',
            'piezas'    => $this->model->orderBy('nombre')->findAll(),
        ]);
    }

    // ── Crear ──────────────────────────────────────────────────

    /**
     * GET /piezas/create
     * Muestra el formulario para crear una nueva pieza maestra.
     */
    public function create(): string
    {
        return view('piezas/_form', [
            'pieza'  => null,
            'errors' => [],
            'old'    => [],
        ]);
    }

    /**
     * POST /piezas
     * Valida y persiste una nueva pieza maestra.
     * El nombre debe ser único en la tabla piezas_maestras.
     */
    public function store()
    {
        $post = $this->request->getPost();

        if (!$this->validate(['nombre' => 'required|max_length[180]|is_unique[piezas_maestras.nombre]'])) {
            return view('piezas/_form', [
                'pieza'  => null,
                'errors' => $this->validator->getErrors(),
                'old'    => $post,
            ]);
        }

        $slug = $this->uniqueSlug($post['nombre'], 'piezas_maestras');

        $this->model->insert(['nombre' => $post['nombre'], 'slug' => $slug]);

        session()->setFlashdata('success', 'Pieza maestra creada correctamente.');
        return $this->htmxRedirect(site_url('/piezas'));
    }

    // ── Editar ─────────────────────────────────────────────────

    /**
     * GET /piezas/{id}/edit
     * Muestra el formulario precargado con los datos de la pieza maestra.
     *
     * @param int $id ID de la pieza maestra
     */
    public function edit(int $id): string
    {
        $pieza = $this->model->find($id);

        return view('piezas/_form', [
            'pieza'  => $pieza,
            'errors' => [],
            'old'    => [],
        ]);
    }

    /**
     * PUT /piezas/{id}
     * Valida y actualiza el nombre de la pieza maestra.
     *
     * @param int $id ID de la pieza maestra
     */
    public function update(int $id)
    {
        $post = $this->request->getPost();

        if (!$this->validate(['nombre' => "required|max_length[180]|is_unique[piezas_maestras.nombre,id,{$id}]"])) {
            return view('piezas/_form', [
                'pieza'  => $this->model->find($id),
                'errors' => $this->validator->getErrors(),
                'old'    => $post,
            ]);
        }

        $slug = $this->uniqueSlug($post['nombre'], 'piezas_maestras', $id);

        $this->model->update($id, ['nombre' => $post['nombre'], 'slug' => $slug]);

        session()->setFlashdata('success', 'Pieza maestra actualizada correctamente.');
        return $this->htmxRedirect(site_url('/piezas'));
    }

    // ── Eliminar ───────────────────────────────────────────────

    /**
     * DELETE /piezas/{id}
     * Elimina la pieza maestra y devuelve el partial HTML actualizado (HTMX swap).
     *
     * @param int $id ID de la pieza maestra
     */
    public function delete(int $id)
    {
        $this->model->delete($id);

        return $this->response->setBody(
            view('piezas/_rows', ['piezas' => $this->model->orderBy('nombre')->findAll()])
        );
    }
}
