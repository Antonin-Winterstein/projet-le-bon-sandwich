<?php
//* Le namespace auquel est lié le fichier
namespace models;

//* Les use pour ne pas avoir à écrire toujours le chemin complet du namespace
use \Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Commande extends Model {
  // const CREATED = 1;
  // const PAID = 2;
  // const PREPARING = 3;
  // const READY = 4;
  // const COMPLETED = 5;

  protected $table = 'commande';
  protected $primaryKey = 'id';
  // protected $fillable = ['id', 'nom', 'mail', 'livraison', 'token'];
  // protected $hidden = ['created_at', 'updated_at'];
  
  public $incrementing = false;
  public $keyType='string';

  public function items() {
    return $this->hasMany('models\Item', 'command_id');
  }

  // public function client() {
  //   return $this->belongsTo('models\Client', 'client_id');
  // }
}