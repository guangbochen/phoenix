<?php
use RedBean_Facade as R;

class Package
{
    public $id;
    public $original_weight;
    public $total_weight;
    public $description;
    public $quantity;
    public $claim_value;
    public $staff_signature;

    public static function show_all()
    {
        try 
        {
            $packages = R::findAll('packages');
            return R::exportAll($packages);
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }

    public static function find($id)
    {
        $package = R::findOne('packages', 'id=?', array($id));
        if ($package)
            return R::exportAll($package);
        else
            return NULL;
    }

    public static function create($input)
    {
        try
        {
            $package= R::dispense('packages');
            $package->original_weight = empty($input->package_original_weight) ? NULL : $input->package_original_weight;
            $package->total_weight    = empty($input->package_total_weight)    ? NULL : $input->package_total_weight;
            $package->description     = empty($input->package_description)     ? NULL : $input->package_description;
            $package->quantity        = empty($input->package_quantity)        ? NULL : $input->package_quantity;
            $package->claim_value     = empty($input->package_claim_value)     ? NULL : $input->package_claim_value;
            $package->staff_signature = empty($input->package_staff_signature) ? NULL : $input->package_staff_signature;
            return R::store($package);
        }
        catch (Exception $e)
        {
            return $e->getMessage();
        }
    }

    public static function update($id, $input)
    {
        $package = R::findOne('packages', 'id=?', array($id));
        if ($package)
        {
            $package->original_weight = empty($input->package_original_weight) ? NULL : $input->package_original_weight;
            $package->total_weight    = empty($input->package_total_weight)    ? NULL : $input->package_total_weight;
            $package->description     = empty($input->package_description)     ? NULL : $input->package_description;
            $package->quantity        = empty($input->package_quantity)        ? NULL : $input->package_quantity;
            $package->claim_value     = empty($input->package_claim_value)     ? NULL : $input->package_claim_value;
            $package->staff_signature = empty($input->package_staff_signature) ? NULL : $input->package_staff_signature;
            return R::store($package);
        }
        else
        {
            return NULL;
        }
    }

    public static function delete($id)
    {
        $package = R::findOne('packages', 'id=?', array($id));
        if ($package)
            R::trash($package);
        else
            return NULL;
    }

    public static function not_exists($input)
    {
        return ((Package::update($input->package_id, $input) == NULL) && ($input->package_original_weight 
                || $input->package_quantity || $input->package_total_weight 
                || $input->package_claim_value || $input->package_staff_signature 
                || $input->package_description));
    }
}
