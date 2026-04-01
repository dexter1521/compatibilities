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

    public function index(): string
    {
        return view('piezas/index', [
            'title'     => 'Piezas Maestras — Compatibilidades',
            'pageTitle' => 'Piezas Maestras',
            'piezas'    => $this->model->orderBy('nombre')->findAll(),
        ]);
    }

    // ── Crear ──────────────────────────────────────────────────

    public function create(): string
    {
        return view('piezas/_form', [
            'pieza'  => null,
            'errors' => [],
            'old'    => [],
        ]);
    }

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

    public function edit(int $id): string
    {
        $pieza = $this->model->find($id);

        return view('piezas/_form', [
            'pieza'  => $pieza,
            'errors' => [],
            'old'    => [],
        ]);
    }

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

    public function delete(int $id)
    {
        $this->model->delete($id);

        return $this->response->setBody(
            view('piezas/_rows', ['piezas' => $this->model->orderBy('nombre')->findAll()])
        );
    }
}
