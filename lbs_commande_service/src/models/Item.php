<?php
//* Le namespace auquel est lié le fichier
namespace lbs\commande\models;

//* Les use pour ne pas avoir à écrire toujours le chemin complet du namespace
use \Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;  

class Item extends Model {
  protected $table = 'item';
  protected $primaryKey = 'id';
}