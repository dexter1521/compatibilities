<?php

namespace App\Controllers;

use App\Models\MarcaModel;
use App\Models\MotoModel;

class Motos extends BaseAdminController
{
    private MotoModel  $motoModel;
    private MarcaModel $marcaModel;

    public function __construct()
    {
        $this->motoModel  = new MotoModel();
        $this->marcaModel = new MarcaModel();
    }

    // ── Listado ────────────────────────────────────────────────

    /**
     * GET /motos
     * Lista todas las motocicletas con su marca.
     */
    public function index(): string
    {
        return view('motos/index', [
            'title'     => 'Motocicletas — Compatibilidades',
            'pageTitle' => 'Motocicletas',
            'motos'     => $this->motoModel->getAllWithMarca(),
        ]);
    }

    // ── Crear ──────────────────────────────────────────────────

    /**
     * GET /motos/create
     * Muestra el formulario para crear una nueva motocicleta.
     * Permite seleccionar una marca existente o escribir una nueva.
     */
    public function create(): string
    {
        return view('motos/_form', [
            'moto'   => null,
            'marcas' => $this->marcaModel->orderBy('nombre')->findAll(),
            'errors' => [],
            'old'    => [],
        ]);
    }

    /**
     * POST /motos
     * Valida y persiste una nueva motocicleta.
     * Si marca_id es 0 y se proporcionó marca_nombre, crea la marca en el mismo request.
     */
    public function store()
    {
        $post = $this->request->getPost();

        // Validación básica
        $rules = [
            'modelo'     => 'required|max_length[150]',
            'anio_desde' => 'permit_empty|integer|greater_than[1900]|less_than[2100]',
            'anio_hasta' => 'permit_empty|integer|greater_than[1900]|less_than[2100]',
            'cilindrada' => 'permit_empty|max_length[50]',
        ];

        // Validar marca: existente o nueva
        $marcaId     = (int) ($post['marca_id'] ?? 0);
        $marcaNombre = trim($post['marca_nombre'] ?? '');

        if ($marcaId === 0 && $marcaNombre === '') {
            $errors = ['marca' => 'Selecciona una marca o ingresa el nombre de una nueva.'];
            if (!$this->validate($rules)) {
                $errors = array_merge($errors, $this->validator->getErrors());
            }
            return view('motos/_form', [
                'moto'   => null,
                'marcas' => $this->marcaModel->orderBy('nombre')->findAll(),
                'errors' => $errors,
                'old'    => $post,
            ]);
        }

        if (!$this->validate($rules)) {
            return view('motos/_form', [
                'moto'   => null,
                'marcas' => $this->marcaModel->orderBy('nombre')->findAll(),
                'errors' => $this->validator->getErrors(),
                'old'    => $post,
            ]);
        }

        // Crear marca nueva si aplica
        if ($marcaId === 0) {
            $slug     = $this->uniqueSlug($marcaNombre, 'marcas');
            $marcaId  = $this->marcaModel->insert(['nombre' => $marcaNombre, 'slug' => $slug], true);
        }

        $modelo = $post['modelo'];
        $slug   = $this->uniqueSlug($marcaNombre ?: '' . '-' . $modelo, 'motocicletas');

        $this->motoModel->insert([
            'marca_id'   => $marcaId,
            'modelo'     => $modelo,
            'anio_desde' => $post['anio_desde'] ?: null,
            'anio_hasta' => $post['anio_hasta'] ?: null,
            'cilindrada' => $post['cilindrada'] ?: null,
            'slug'       => $slug,
        ]);

        session()->setFlashdata('success', 'Motocicleta creada correctamente.');
        return $this->htmxRedirect(site_url('/motos'));
    }

    // ── Editar ─────────────────────────────────────────────────

    /**
     * GET /motos/{id}/edit
     * Muestra el formulario precargado con los datos de la motocicleta.
     *
     * @param int $id ID de la motocicleta
     */
    public function edit(int $id): string
    {
        $moto = $this->motoModel->getWithMarca($id);

        if (!$moto) {
            return view('motos/_form', [
                'moto'   => null,
                'marcas' => $this->marcaModel->orderBy('nombre')->findAll(),
                'errors' => ['general' => 'Motocicleta no encontrada.'],
                'old'    => [],
            ]);
        }

        return view('motos/_form', [
            'moto'   => $moto,
            'marcas' => $this->marcaModel->orderBy('nombre')->findAll(),
            'errors' => [],
            'old'    => [],
        ]);
    }

    /**
     * PUT /motos/{id}
     * Valida y actualiza los datos de la motocicleta.
     *
     * @param int $id ID de la motocicleta
     */
    public function update(int $id)
    {
        $post = $this->request->getPost();

        $moto = $this->motoModel->find($id);
        if (!$moto) {
            session()->setFlashdata('error', 'Motocicleta no encontrada.');
            return $this->htmxRedirect(site_url('/motos'));
        }

        $rules = [
            'marca_id'   => 'required|is_natural_no_zero',
            'modelo'     => 'required|max_length[150]',
            'anio_desde' => 'permit_empty|integer|greater_than[1900]|less_than[2100]',
            'anio_hasta' => 'permit_empty|integer|greater_than[1900]|less_than[2100]',
            'cilindrada' => 'permit_empty|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return view('motos/_form', [
                'moto'   => $this->motoModel->getWithMarca($id),
                'marcas' => $this->marcaModel->orderBy('nombre')->findAll(),
                'errors' => $this->validator->getErrors(),
                'old'    => $post,
            ]);
        }

        $slug = $this->uniqueSlug($post['modelo'], 'motocicletas', $id);

        $this->motoModel->update($id, [
            'marca_id'   => (int) $post['marca_id'],
            'modelo'     => $post['modelo'],
            'anio_desde' => $post['anio_desde'] ?: null,
            'anio_hasta' => $post['anio_hasta'] ?: null,
            'cilindrada' => $post['cilindrada'] ?: null,
            'slug'       => $slug,
        ]);

        session()->setFlashdata('success', 'Motocicleta actualizada correctamente.');
        return $this->htmxRedirect(site_url('/motos'));
    }

    // ── Eliminar ───────────────────────────────────────────────

    /**
     * DELETE /motos/{id}
     * Elimina la motocicleta y devuelve el partial HTML actualizado (HTMX swap).
     *
     * @param int $id ID de la motocicleta
     */
    public function delete(int $id)
    {
        $this->motoModel->delete($id);

        return $this->response->setBody(
            view('motos/_rows', ['motos' => $this->motoModel->getAllWithMarca()])
        );
    }
}
