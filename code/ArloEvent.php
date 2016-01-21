<?php

class ArloEvent extends DataObject {

    /**
     * @var array
     */
    private static $db = array(
        'EventID' => 'Varchar(255)',
        'Name' => 'Varchar(255)',
        'ViewUri' => 'Varchar(255)',
        'Code' => 'Varchar(255)',
        'Summary' => 'Text',
        'Description' => 'HTMLText',
        'StartDateTime' => 'Date',
        'EndDateTime' => 'Date',
        'Location' => 'Varchar(255)',
        'TemplateCode' => 'Varchar(255)',
        'Provider' => 'Varchar(255)',
        'IsPrivate' => 'Boolean',
        'Archive' => 'Boolean'
    );

    /**
     * @var array
     */
    private static $has_one = array(
        'Image' => 'Image'
    );

    /**
     * [$summary_fields description]
     * @var array
     */
    private static $summary_fields = array(
        'EventID',
        'Code',
        'Name',
        'Location',
        'StartDateTime',
        'EndDateTime'
    );

}