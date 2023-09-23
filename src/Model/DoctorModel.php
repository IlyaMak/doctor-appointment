<?php

namespace App\Model;

use App\Entity\Specialty;
use App\Entity\User;

class DoctorModel
{
    public ?Specialty $specialty;
    public ?User $doctor;

    public function __construct(?Specialty $specialty, ?User $doctor)
    {
        $this->specialty = $specialty;
        $this->doctor = $doctor;
    }
}
