<?php
use RedBean_Facade as R;

$app->get('/seeds', 'seed');
function seed()
{
    seed_courier();
    seed_sender(20);
    seed_receiver(20);
    seed_package(10);
    seed_waybill(10);
}

// Seed dummy data to courier
function seed_courier()
{
    $app   = \Slim\Slim::getInstance();
    $faker = Faker\Factory::create();

    $courier1 = R::dispense('couriers');
    $courier1->name = 'EMS';
    /* $courier1->name   = '汇通快递'; */
    R::store($courier1);

    $courier2 = R::dispense('couriers');
    $courier2->name = 'ABC';
    /* $courier2->name   = '申通快递'; */
    R::store($courier2);

    $courier3 = R::dispense('couriers');
    $courier3->name = 'BAR';
    /* $courier3->name   = '顺丰快递'; */
    R::store($courier3);

    $courier4 = R::dispense('couriers');
    $courier4->name = 'FOO';
    /* $courier4->name   = '圆通快递'; */
    R::store($courier4);
}

// Seed dummy data to waybill
function seed_sender($n)
{
    $app   = \Slim\Slim::getInstance();
    $faker = Faker\Factory::create();

    for ($i = 0; $i < $n; $i++)
    {
        $sender = R::dispense('senders');
        $sender->name     = $faker->name;
        $sender->phone    = $faker->phoneNumber;
        $sender->company  = $faker->company;
        $sender->address  = $faker->address;
        $sender->city     = $faker->city;
        $sender->state    = $faker->state;
        $sender->country  = $faker->country;
        $sender->postcode = $faker->postcode;
        R::store($sender);
    }
}

function seed_receiver($n)
{
    $app   = \Slim\Slim::getInstance();
    $faker = Faker\Factory::create();

    for ($i = 0; $i < $n; $i++)
    {
        $receiver = R::dispense('receivers');
        $receiver->name     = $faker->name;
        $receiver->phone    = $faker->phoneNumber;
        $receiver->company  = $faker->company;
        $receiver->address  = $faker->address;
        $receiver->city     = $faker->city;
        $receiver->state    = $faker->state;
        $receiver->country  = $faker->country;
        $receiver->postcode = $faker->postcode;
        R::store($receiver);
    }
}

function seed_package($n)
{
    $app   = \Slim\Slim::getInstance();
    $faker = Faker\Factory::create();

    for ($i = 0; $i < $n; $i++)
    {
        $package = R::dispense('packages');
        $package->original_weight = $faker->randomDigitNotNull;
        $package->total_weight    = $faker->randomDigitNotNull;
        $package->description     = $faker->paragraph($nbSentences = 1);
        $package->quantity        = $faker->randomDigitNotNull;
        $package->claim_value     = $faker->randomNumber(1000, 10000);
        $package->staff_signature = $faker->lastName;
        R::store($package);
    }
}

function seed_waybill($n)
{
    $app   = \Slim\Slim::getInstance();
    $faker = Faker\Factory::create();

    $package_id = 1;
    for ($i = 0; $i < $n; $i++)
    {
        $waybill = R::dispense('waybills');

        $waybill->tracking_id = tracking_id();
        $waybill->sender_id   = $faker->randomNumber(1, 20);
        $waybill->receiver_id = $faker->randomNumber(1,20);
        $waybill->courier_id  = $faker->randomNumber(1, 4);
        $waybill->package_id  = $package_id;

        $date = $faker->dateTimeThisYear;
        $waybill->order_date = $date->format('d/m/Y');

        if ($i < 5)
            $waybill->location = 'sydney';
        else
            $waybill->location = 'redfern';

        $waybill->postage        = $faker->randomNumber(1000, 10000);
        $waybill->insurance      = $faker->randomNumber(1000, 10000);
        $waybill->tax            = $faker->randomNumber(1000, 10000);
        $waybill->packing_charge = $faker->randomNumber(1000, 10000);
        $waybill->total_price    = $faker->randomNumber(1000, 10000);
        $waybill->agent_number   = $faker->randomNumber(1000, 10000);
        $waybill->agent_price    = $faker->randomNumber(1000, 10000);
        $waybill->note           = $faker->paragraph($nbSentences = 1);
        $waybill->express_number = $faker->md5;

        $package_id += 1;
        R::store($waybill);
    }
}

function tracking_id()
{
    // Get last id
    $waybill = R::getRow('SELECT * FROM waybills ORDER BY id DESC LIMIT 1');
    $id = ($waybill == NULL) ? 1 : ++$waybill['id'];

    if ($id < 10)
        return 'PHE000000'.$id.'AU';
    else if ($id < 100)
        return 'PHE00000'.$id.'AU';
    else if ($id < 1000)
        return 'PHE0000'.$id.'AU';
    else if ($id < 10000)
        return 'PHE000'.$id.'AU';
    else if ($id < 100000)
        return 'PHE00'.$id.'AU';
    else
        throw new Exception('5 digits only');
}
