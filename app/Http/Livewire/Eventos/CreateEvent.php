<?php

namespace App\Http\Livewire\Eventos;

use App\Models\Fotografo;
use App\Models\Organizador;
use App\Models\Cliente;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Evento;
use App\Models\SolicitudClienteOrganizador;
use App\Models\SolicitudFotografoOrganizador;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use stdClass;


class CreateEvent extends Component
{
    use WithFileUploads;

    public $titulo, $descripcion, $direccion, $fecha, $hora, $ubicacion, $foto, $identificador;
    public $searchFotografo, $searchCliente;
    public $open = false;
    public $solicitudesFotografos;
    public $solicitudesClientes;

    protected $listeners = ['enlistarSolicitudFotografo', 'enlistarSolicitudCliente'];

    public function render()
    {


        $fotografos  = Fotografo::join('users', 'users.id', 'fotografos.user_id')->select('fotografos.*')->where('users.name', 'like', '%' . $this->searchFotografo . '%')
            ->orWhere('users.email', 'like', '%' . $this->searchFotografo . '%')->get();

        $clientes = Cliente::join('users', 'users.id', 'clientes.user_id')->select('clientes.*')->where('users.name', 'like', '%' . $this->searchCliente . '%')
            ->orWhere('users.email', 'like', '%' . $this->searchCliente . '%')->get();

        return view('livewire.eventos.create-event', compact('fotografos', 'clientes'));
    }

    protected $rules = [
        'titulo' => 'required|max:25',
        'descripcion' => 'max:50',
        'direccion' => 'required|max:30',
        'fecha' => 'required|date',
        'hora' => 'required|date_format:H:i',
    ];

    public function mount()
    {
        $this->identificador = rand();
        $this->solicitudesFotografos = new Collection();
        $this->solicitudesClientes = new Collection();
    }

    public function updatedOpen()
    {
        if ($this->open == false) {
            $this->reset(['titulo', 'descripcion', 'direccion', 'fecha', 'hora', 'foto', 'searchFotografo', 'searchCliente', 'solicitudesFotografos', 'solicitudesClientes']);
            $this->solicitudesFotografos = new Collection();
            $this->solicitudesClientes = new Collection();
            $this->identificador = rand();
        }
    }


    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function enlistarSolicitudFotografo($fotografoId)
    {
        /* $solocitud = new stdClass;
        $solocitud->fotografo_id = $fotografoId;
        $solocitud->organizador_id = Auth::user()->organizador->id; */

        if ($this->solicitudesFotografos->contains('fotografo_id', $fotografoId)) {
            $this->solicitudesFotografos = $this->solicitudesFotografos->reject(function ($elemento) use ($fotografoId) {
                return $elemento['fotografo_id'] === $fotografoId;
            });
        } else {
            $solocitud = [
                'fotografo_id' => $fotografoId,
            ];

            $this->solicitudesFotografos->push($solocitud);
        }


        //dd($this->solicitudes);
        //dd($this->solicitudes->contains('fotografo_id',10));

    }

    public function enlistarSolicitudCliente($clienteId)
    {

        if ($this->solicitudesClientes->contains('cliente_id', $clienteId)) {
            $this->solicitudesClientes = $this->solicitudesClientes->reject(function ($elemento) use ($clienteId) {
                return $elemento['cliente_id'] === $clienteId;
            });
        } else {
            $solocitud = [
                'cliente_id' => $clienteId,
            ];

            $this->solicitudesClientes->push($solocitud);
        }
    }


    public function save()
    {

        $this->validate();

        $evento = Evento::create([
            'nombre' => $this->titulo,
            'descripcion' => $this->descripcion,
            'direccion' => $this->direccion,
            'fecha' => $this->fecha,
            'hora' => $this->hora,
            'ubicacion' => $this->ubicacion,
            'organizadores_id' => Auth::user()->organizador->id,
        ]);

    
        if ($this->foto) {

            $nombre = $this->foto->getClientOriginalName();
            $ruta = $this->foto->storeAs('public/eventos/' ,$nombre);
            $url = Storage::url($ruta);
            $evento->photo_path = $url;
            $evento->save();
        }



        foreach ($this->solicitudesFotografos as $solicitudfotografo) {

            SolicitudFotografoOrganizador::create([

                'emisor' => Auth::user()->organizador->id,
                'receptor' => $solicitudfotografo['fotografo_id'],
                'estado' => 'pendiente',
                'organizadores_id' => Auth::user()->organizador->id,
                'fotografos_id' => $solicitudfotografo['fotografo_id'],
                'eventos_id' => $evento->id,
            ]);
        }

        foreach ($this->solicitudesClientes as $solicitudCliente) {

            SolicitudClienteOrganizador::create([

                'emisor' => Auth::user()->organizador->id,
                'receptor' => $solicitudCliente['cliente_id'],
                'estado' => 'pendiente',
                'organizadores_id' => Auth::user()->organizador->id,
                'clientes_id' => $solicitudCliente['cliente_id'],
                'eventos_id' => $evento->id,
            ]);
        }


        $this->reset(['titulo', 'descripcion', 'direccion', 'fecha', 'hora', 'foto', 'searchFotografo', 'searchCliente', 'solicitudesFotografos', 'solicitudesClientes']);
        $this->solicitudesFotografos = new Collection();
        $this->solicitudesClientes = new Collection();
        $this->emitTo('eventos.show-events', 'render');
        $this->emit('alert', ['mensaje' => 'El evento se creo satisfactoriamente', 'icono' => 'success']);
        $this->open = !$this->open;
    }
}
