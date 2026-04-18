<?php

namespace App\Controllers;

use App\Models\MarcaModel;

class Marcas extends BaseAdminController
{
    private MarcaModel $model;

    public function __construct()
    {
        $this->model = new MarcaModel();
    }

    // ── Listado ────────────────────────────────────────────────

    public function index(): string
    {
        return view('marcas/index', [
            'title'  => 'Marcas — Compatibilidades',
            'marcas' => $this->model->orderBy('nombre')->findAll(),
        ]);
    }

    // ── Crear ──────────────────────────────────────────────────

    public function create(): string
    {
        return view('marcas/_form', [
            'marca'  => null,
            'errors' => [],
            'old'    => [],
        ]);
    }

    public function store()
    {
        $post = $this->request->getPost();

        if (!$this->validate(['nombre' => 'required|trim|max_length[120]|is_unique[marcas.nombre]'])) {
            return view('marcas/_form', [
                'marca'  => null,
                'errors' => $this->validator->getErrors(),
                'old'    => $post,
            ]);
        }

        $slug = $this->uniqueSlug($post['nombre'], 'marcas');
        $this->model->insert(['nombre' => $post['nombre'], 'slug' => $slug, 'activo' => 1]);

        session()->setFlashdata('success', 'Marca creada correctamente.');
        return $this->htmxRedirect(site_url('/marcas'));
    }

    // ── Editar ─────────────────────────────────────────────────

    public function edit(int $id): string
    {
        return view('marcas/_form', [
            'marca'  => $this->model->find($id),
            'errors' => [],
            'old'    => [],
        ]);
    }

    public function update(int $id)
    {
        $post = $this->request->getPost();

        if (!$this->validate(['nombre' => "required|trim|max_length[120]|is_unique[marcas.nombre,id,{$id}]"])) {
            return view('marcas/_form', [
                'marca'  => $this->model->find($id),
                'errors' => $this->validator->getErrors(),
                'old'    => $post,
            ]);
        }

        $slug = $this->uniqueSlug($post['nombre'], 'marcas', $id);
        $this->model->update($id, [
            'nombre' => $post['nombre'],
            'slug'   => $slug,
            'activo' => (int) ($post['activo'] ?? 0),
        ]);

        session()->setFlashdata('success', 'Marca actualizada correctamente.');
        return $this->htmxRedirect(site_url('/marcas'));
    }

    // ── Toggle activo ──────────────────────────────────────────

    public function toggle(int $id)
    {
        $marca = $this->model->find($id);
        if ($marca) {
            $this->model->update($id, ['activo' => $marca['activo'] ? 0 : 1]);
        }

        return view('marcas/_rows', [
            'marcas' => $this->model->orderBy('nombre')->findAll(),
        ]);
    }

    // ── Eliminar ───────────────────────────────────────────────

    public function delete(int $id)
    {
        $this->model->delete($id);

        return view('marcas/_rows', [
            'marcas' => $this->model->orderBy('nombre')->findAll(),
        ]);
    }
}
