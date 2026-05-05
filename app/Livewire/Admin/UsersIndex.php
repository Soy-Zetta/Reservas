<?php

namespace App\Livewire\Admin; // 🟢 ESTA ES LA DIRECCIÓN CORRECTA

use Livewire\Component;
use App\Models\User;
use Livewire\WithPagination;

class UsersIndex extends Component
{
    use WithPagination; 

    public $search = '';

    // Obligamos a Livewire a usar los botones de AdminLTE (Bootstrap)
    protected $paginationTheme = 'bootstrap';

    // Si el usuario escribe algo en el buscador, lo devolvemos a la página 1
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Filtramos la base de datos según lo que haya en la variable $search
        $users = User::where('name', 'LIKE', '%' . $this->search . '%')
                     ->orWhere('email', 'LIKE', '%' . $this->search . '%')
                     ->paginate(10); 

        return view('livewire.admin.users-index', compact('users'));
    }
}