<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class ReservaDepartamental extends Mailable
{
    use Queueable, SerializesModels;

    public $reserva;
    public $requerimientos;
    public $departamentosRelevantes;

    public function __construct($reserva, $requerimientos)
    {
        $this->reserva = $reserva;
        $this->requerimientos = $requerimientos;
        $this->departamentosRelevantes = $this->getRelevantDepartments();
    }

    private function getRelevantDepartments()
    {
        $departamentos = [];
        $requirementsConfig = config('requirements');
        
        foreach ($this->requerimientos as $req) {
            if (isset($requirementsConfig[$req])) {
                $depto = $requirementsConfig[$req]['department'];
                if (!in_array($depto, $departamentos)) {
                    $departamentos[] = $depto;
                }
            }
        }
        
        return $departamentos;
    }

    public function build()
    {
        $subject = "Reserva #{$this->reserva->id} - " 
                 . implode(', ', array_map('ucfirst', $this->departamentosRelevantes));
        
        return $this->subject($subject)
                    ->view('emails.departamental');
    }
}