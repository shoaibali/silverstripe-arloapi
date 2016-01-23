<?php

class ArloEventAdmin extends ModelAdmin {
    private static $managed_models = array(
        'ArloEvent'
    );
    private static $url_segment = 'arlo-events';
    private static $menu_title = 'Arlo Events';
}