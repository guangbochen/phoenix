function waybill(value)
{
    var courier_id    = (!value.courier) ? null : value.courier.id;
    var courier_value = (!value.courier) ? null : value.courier.name;
    var courier_name  = '';

    if (courier_value)
    {
        if      (courier_value === 'huitongkuaidi') courier_name = '汇通快递';
        else if (courier_value === 'shentong')      courier_name = '申通快递';
        else if (courier_value === 'ems')           courier_name = 'EMS';
        else if (courier_value === 'shunfeng')      courier_name = '顺丰快递';
        else if (courier_value === 'yuantong')      courier_name = '圆通快递';
        else if (courier_value === 'yunda')         courier_name = '韵达快递';
    }

    var sender_id       = (!value.sender) ? null : value.sender.id;
    var sender_identity = (!value.sender) ? null : value.sender.identity;
    var sender_name     = (!value.sender) ? null : value.sender.name;
    var sender_phone    = (!value.sender) ? null : value.sender.phone;
    var sender_company  = (!value.sender) ? null : value.sender.company;
    var sender_address  = (!value.sender) ? null : value.sender.address;
    var sender_city     = (!value.sender) ? null : value.sender.city;
    var sender_state    = (!value.sender) ? null : value.sender.state;
    var sender_country  = (!value.sender) ? null : value.sender.country;
    var sender_postcode = (!value.sender) ? null : value.sender.postcode;
    var sender_frontside_photo = (!value.sender) ? null : value.sender.frontside_photo;
    var sender_backside_photo  = (!value.sender) ? null : value.sender.backside_photo;
    var sender_signature_photo = (!value.sender) ? null : value.sender.signature_photo;

    var receiver_id       = (!value.receiver) ? null : value.receiver.id;
    var receiver_name     = (!value.receiver) ? null : value.receiver.name;
    var receiver_phone    = (!value.receiver) ? null : value.receiver.phone;
    var receiver_company  = (!value.receiver) ? null : value.receiver.company;
    var receiver_address  = (!value.receiver) ? null : value.receiver.address;
    var receiver_city     = (!value.receiver) ? null : value.receiver.city;
    var receiver_state    = (!value.receiver) ? null : value.receiver.state;
    var receiver_country  = (!value.receiver) ? null : value.receiver.country;
    var receiver_postcode = (!value.receiver) ? null : value.receiver.postcode;

    var package_id              = (!value.package) ? null : value.package.id;
    var package_original_weight = (!value.package) ? null : value.package.original_weight;
    var package_quantity        = (!value.package) ? null : value.package.quantity;
    var package_total_weight    = (!value.package) ? null : value.package.total_weight;
    var package_claim_value     = (!value.package) ? null : value.package.claim_value;
    var package_staff_signature = (!value.package) ? null : value.package.staff_signature;
    var package_description     = (!value.package) ? null : value.package.description;

    var city_id   = (!value.city) ? null : value.city.id;
    var city_name = (!value.city) ? null : value.city.name;

    return {
        id             : value.id,
        courier_id     : courier_id,
        tracking_id    : value.tracking_id,
        order_date     : value.order_date,
        flight_date    : value.flight_date,
        location       : value.location,
        postage        : value.postage,
        insurance      : value.insurance,
        tax            : value.tax,
        packing_charge : value.packing_charge,
        total_price    : value.total_price,
        agent_number   : value.agent_number,
        agent_price    : value.agent_price,
        courier_value  : courier_value,
        courier_name   : courier_name,
        express_number : value.express_number,
        note           : value.note,
        waybill_status : value.waybill_status,
        statuses       : value.ownStatuses,

        city_id        : city_id,
        city_name      : city_name,

        // Sender info
        sender_id       : sender_id,
        sender_identity : sender_identity,
        sender_phone    : sender_phone,
        sender_name     : sender_name,
        sender_company  : sender_company,
        sender_address  : sender_address,
        sender_city     : sender_city,
        sender_state    : sender_state,
        sender_country  : sender_country,
        sender_postcode : sender_postcode,
        sender_frontside_photo : sender_frontside_photo,
        sender_backside_photo  : sender_backside_photo,
        sender_signature_photo : sender_signature_photo,

        // Receiver info
        receiver_id       : receiver_id,
        receiver_phone    : receiver_phone,
        receiver_name     : receiver_name,
        receiver_company  : receiver_company,
        receiver_address  : receiver_address,
        receiver_city     : receiver_city,
        receiver_state    : receiver_state,
        receiver_country  : receiver_country,
        receiver_postcode : receiver_postcode,

        // Package info
        package_id              : package_id,
        package_original_weight : package_original_weight,
        package_quantity        : package_quantity,
        package_total_weight    : package_total_weight,
        package_claim_value     : package_claim_value,
        package_staff_signature : package_staff_signature,
        package_description     : package_description,
    };
}
