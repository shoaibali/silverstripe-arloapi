<?php

/**
 * Base class for Arlo Service
 *
 */

class EventSearch extends Object
{
    protected $platform = 'demo';

    protected $endpoint = 'http://api.arlo.co/%s/api/2012-02-01/pub';

    private $cache = null;

    private $format = 'json';

    protected $events = array();

    private $eventsSearchPath = '/resources/eventsearch';

    private $eventFields = array('EventID',
                            'Name',
                            'ViewUri',
                            'Code',
                            'Summary',
                            'Description',
                            'StartDateTime',
                            'EndDateTime',
                            'Location',
                            'Categories',
                            'Tags',
                            'TemplateCode',
                            'Provider',
                            'IsPrivate',
                            'AdvertisedOffers'
    );

    /**
     * @param string $platform
     * @return ArloService
     */
    public function __construct($platform = '')
    {

        if ($platform) {
            // $platform = self::config()->platform;
            // include the platform in to endpoint
            $this->endpoint = sprintf($this->endpoint, $platform);
        }

        // return Injector::inst()->create($platform);
    }

    /**
     * @return Zend_Cache_Frontend
     */
    public function getCache()
    {
        if (!$this->cache) {
            $this->cache = SS_Cache::factory('EventSearch');
        }

        return $this->cache;
    }

    /**
     * Load currencies from Cache
     *
     * @return mixed
     */
    public function loadFromCache()
    {
        return ($cached = $this->getCache()->load($this->getCacheKey()))
            ? unserialize($cached)
            : null;
    }

    /**
     * Save events to cache
     *
     * @param $events
     * @return mixed
     */
    public function saveToCache($events)
    {
        return $this->getCache()->save(serialize($events), $this->getCacheKey());
    }

    // TODO - Deal with deleted events in Arlo
    private function syncToDB($events) {
        $events = $events->Items;
        foreach($events as $event) {
            //identify the event by its ID which is to be unique
            $arloevent = ArloEvent::get()->filter(array('EventID' => $event->EventID))->First();

            // if it doesnt exist in DB make a new one!
            if (!$arloevent) {
                $arloevent = new ArloEvent();
            }

            $name = isset($event->Name)? $event->Name : '';
            $summary = isset($event->Summary)? $event->Summary : '';
            $description = isset($event->Description->Text)? $event->Description->Text : '';
            $link = isset($event->ViewUri)? $event->ViewUri : '';
            $location = isset($event->Location)? $event->Location->Name : '';
            $start = isset($event->StartDateTime)? $event->StartDateTime : '';
            $end = isset($event->EndDateTime)? $event->EndDateTime : '';
            $code = isset($event->Code)? $event->Code : '';
            $templatecode = isset($event->TemplateCode)? $event->TemplateCode : '';
            $provider = isset($event->Provider)? $event->Provider : '';

            $private = isset($event->IsPrivate)? $event->IsPrivate : false;
            $private = ($private == 'false')? false : true;

            $startdatetime = new SS_Datetime();
            $startdatetime->setValue($event->StartDateTime);

            $enddatetime = new SS_Datetime();
            $enddatetime->setValue($event->EndDateTime);

            $arloevent->EventID = $event->EventID;
            $arloevent->Name = $name;
            $arloevent->ViewUri = $link;
            $arloevent->Code = $code;
            $arloevent->Summary = $summary;
            $arloevent->Description = $description;
            $arloevent->StartDateTime = $startdatetime;
            $arloevent->EndDateTime = $enddatetime;
            $arloevent->Location = $location;
            $arloevent->TemplateCode = $templatecode;
            $arloevent->Provider = $provider;
            $arloevent->IsPrivate = $private;

            $arloevent->write();

        }
    }

    private function getCacheKey()
    {
        return __CLASS__;
    }



    public function getEvents(){

       if (empty($this->events)) {
            $this->loadEvents();
        }
        return $this->events;

    }


    /**
     * Load events, checks cache first, otherwise calls searchEvents
     */
    public function loadEvents($force = false)
    {
        if ($force || !($events = $this->loadFromCache())) {
            $events = $this->searchEvents();
            $this->saveToCache($events);
            $this->syncToDB($events);
        }
        $this->events = $events;
    }




    public function searchEvents()
    {
        $fields = "?fields=" . implode(',', $this->eventFields);
        $url = $this->endpoint . $this->eventsSearchPath . $fields;

        $client = new RestfulService($url, -1);
        $client = $client->request();
        $response = $client->getBody();

        $events = Convert::json2obj($response);

        return $events;
    }

}