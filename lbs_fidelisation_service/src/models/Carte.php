<?php
//* Le namespace auquel est lié le fichier
namespace lbs\fidelisation\models;

//* Les use pour ne pas avoir à écrire toujours le chemin complet du namespace
use \Illuminate\Database\Eloquent\Model;

class Carte extends Model {

  protected $table = 'carte_fidelite';
  protected $primaryKey = 'id';
  public $keyType='string';

  public function commande() {
      return $this->hasMany('lbs\fidelisation\models\Commande', 'carte_id');
  }
  // protected $fillable = ['id', 'nom', 'mail', 'livraison', 'token'];
  // protected $hidden = ['created_at', 'updated_at'];
}