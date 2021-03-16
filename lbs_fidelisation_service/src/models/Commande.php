<?php
//* Le namespace auquel est lié le fichier
namespace lbs\fidelisation\models;

//* Les use pour ne pas avoir à écrire toujours le chemin complet du namespace
use \Illuminate\Database\Eloquent\Model;

class Commande extends Model {

    protected $table = 'commande';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $keyType='string';
    // protected $fillable = ['id', 'nom', 'mail', 'livraison', 'token'];
    // protected $hidden = ['created_at', 'updated_at'];
}