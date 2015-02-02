<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:30
 */

namespace Catalog\SubaruBundle\Models;

use Catalog\CommonBundle\Components\Constants;

use Catalog\SubaruBundle\Components\SubaruConstants;

class SubaruVinModel extends SubaruCatalogModel {

    public function getVinFinderResult($vin)
    {
        $sql = "
        SELECT *
        FROM vin
        WHERE vin = :vin
        LIMIT 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('vin', $vin);
        $query->execute();

        $aData = $query->fetch();
      
        $sql = "
        SELECT desc_en
        FROM models
        WHERE model_code = :model_code
        LIMIT 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('model_code', $aData['Model_code']);
        $query->execute();

        $aModelDesc = $query->fetch();
		
        
        $sql = "
        SELECT *
        FROM body
        WHERE body = :modificationCode
        LIMIT 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('modificationCode', $aData['Body_model']);
        $query->execute();

        $aModel = $query->fetch();

        $sql = "
        SELECT *
        FROM model_changes
        WHERE catalog = :catalog
        AND model_code = :model_code
        AND change_abb = :change_abb
        LIMIT 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('catalog', $aData['catalog']);
        $query->bindValue('model_code', $aModel['model_code']);
        $query->bindValue('change_abb', substr($aData['Body_model'], 3, 1));
        $query->execute();

        $aModif = $query->fetch();
        
        $sql = "
        SELECT *
        FROM body_desc
        WHERE model_code = :model_code
        AND id = :id
        LIMIT 1
        ";

        $query = $this->conn->prepare($sql);
        $query->bindValue('model_code', $aModel['model_code']);
        $query->bindValue('id', trim($aModel['body_desc_id']));
        $query->execute();

        $aCompl = $query->fetch();
		$ch = array();
		foreach($aCompl as $index =>$value )	
        {	
        	$sqlAbb = "
        SELECT param_name
        FROM model_desc
        WHERE catalog = :regionCode
        AND model_code = :model_code 
        AND param_abb = :item1
        ";

        $query = $this->conn->prepare($sqlAbb);
        $query->bindValue('regionCode', $aData['catalog']);
        $query->bindValue('model_code', $aModel['model_code']);
        $query->bindValue('item1', $value);
        $query->execute();

        $sDesc[$index] = $query->fetch();
       
         $ch[$index] ='('.$value.') '.$sDesc[$index]['param_name'];
         		
		}






        $result = array();

        if ($aData) {
            $result = array(
                'region' => $aData['catalog'],
                'model' => '('.$aData['Model_code'].') '.$aModelDesc['desc_en'],
                'prod_year' => $aData['date1'],
                'modification' => $aModif['change_abb'].$aModif['desc_en'],
                'complectation' => $aCompl['f1'],
                'body' => $ch['body'],
                'engine' => $ch['engine1'],
                'train' => $ch['train'],
                'trans' => $ch['trans'],
                'sus' => $ch['sus'],                
                'ext_color' => $aData['color_code'],
                'Trim_code'=>$aData['Trim_code'],
                Constants::PROD_DATE => $aData['date1']
            );
        }

        return $result;
    }
        public function getArticuls($regionCode, $cd, $modificationCode, $subGroupCode, $pncCode, $modelCode)
    {
        $sqlArticuls = "
        SELECT *
        FROM part_catalog
        WHERE catalog = :regionCode
          AND model_code = :model_code
          AND f8 = :modificationCode
          AND sec_group = :subGroupCode
          AND part_code = :pncCode
        ";

        $query = $this->conn->prepare($sqlArticuls);
        $query->bindValue('regionCode', $regionCode);
        $query->bindValue('model_code', $modelCode);
        $query->bindValue('modificationCode', substr($modificationCode, 0, 1));
        $query->bindValue('subGroupCode', $subGroupCode);
        $query->bindValue('pncCode', $pncCode);
        $query->execute();

        $aData = $query->fetchAll();
       

    /*    $sqlArticulsDescr = "
        SELECT pd.id, GROUP_CONCAT(pd.descr SEPARATOR '; ') as descr
        FROM part_descs pd
        WHERE pd.catalog = ?
          AND pd.cd = ?
          AND pd.catalog_number = ?
          AND pd.lang = 1
          AND pd.id IN (?)
        GROUP BY pd.id
        ";

        $query = $this->conn->executeQuery($sqlArticulsDescr, array(
            $regionCode,
            $cd,
            $modificationCode,
            array_column($aData, 'desc_id')
        ), array(
            \PDO::PARAM_STR,
            \PDO::PARAM_STR,
            \PDO::PARAM_STR,
            \Doctrine\DBAL\Connection::PARAM_STR_ARRAY
        ));

        $aDataDescr = $query->fetchAll();
        $aDataDescr = array_combine(array_column($aDataDescr, 'id'), array_column($aDataDescr, 'descr'));
*/
        $articuls = array();
        foreach ($aData as $item) {
            $articuls[$item['part_number']] = array(
                Constants::NAME =>$item['model_restrictions'],
                Constants::OPTIONS => array(
                    Constants::QUANTITY => '',
                    Constants::START_DATE => $item['sdate'],
                    Constants::END_DATE => $item['edate']
                )
            );
        }
        

        return $articuls;
    }

} 