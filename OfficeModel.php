<?php
namespace App\Model;

use Nette;
use Nette\Database\Table\Selection;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Nette\Utils\DateTime;

class OfficeModel
{
    use Nette\SmartObject;
    /** @var Nette\Database\Explorer  */
    private $database;

    public function __construct(Nette\Database\Explorer $database)
    {
        $this->database = $database;
    }


    public function vykaz($values)
    {
         $this->database->beginTransaction();
            try {
            $this->database->table('vykaz_prace')->insert([
            'user_id'=>$values['user_id'],
            'ukol'=>$values['ukol'],
            'datum_vykazu'=>$values['datum_vykazu'],
            'datum_pridani'=>$values['datum_pridani'],
            'cas'=>$values['cas'],
            'poznamka'=>$values['poznamka'],
            'podukol'=>$values['podukol'],
            'popis'=>$values['popis'],
        ]);
        $this->database->commit();
        } catch (PDOException $e) {
            $this->database->rollBack();
            throw $e; // pošlu to dál
        }
    }
    public function getVykaz(int $user_id)
    {
        return $this->database->table('vykaz_prace')->where('user_id',$user_id)->order('datum_vykazu ASC');

        $vykaz['den'][]='';
        $vykaz['cas'][]='';
        $vykaz['cas_celkem'][]=0;

        foreach($vykazyDb as $index => $vykazy){
            $datumRokMesic = DateTime::from($vykazy->datum_vykazu);

            $vykaz['den'][$index] = DateTime::from($vykazy->datum_vykazu)->format('d');
            $vykaz['cas'][$index] = DateTime::from($vykazy->cas)->format('H:i');
        }

        $vykaz['cas_celkem'][]='';

        foreach($vykaz['cas'] as $index => $value) {
            if($index == 0){ continue;}
            else{
            list($hour, $minute) = explode(':', $value);

            $hour = (int) $hour;
            $minute = (int) $minute;
            if($hour == null){
                $vykaz['cas_celkem'][$index] = $minute;
            }else{
                $vykaz['cas_celkem'][$index] = ( $hour * 60 ) + $minute;
            }

            }
        }


        $vykaz['rok']  = $datumRokMesic->format('Y');
        $vykaz['mesic']  = $datumRokMesic->format('m');

        return $vykaz;
    }

}