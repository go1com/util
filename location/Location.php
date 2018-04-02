<?php

namespace go1\util\location;

use go1\util\Text;
use JsonSerializable;
use stdClass;

class Location implements JsonSerializable
{

    public $id;
    public $title;
    public $instanceId;
    public $country;
    public $administrativeArea;
    public $subAdministrativeArea;
    public $locality;
    public $dependentLocality;
    public $thoroughfare;
    public $premise;
    public $subPremise;
    public $organisationName;
    public $nameLine;
    public $postalCode;
    public $authorId;
    public $created;
    public $updated;

    /** @var Location */
    public $original;

    public static function create(stdClass $input): Location
    {
        Text::purify(null, $input);

        $location = new Location;
        $location->id = $input->id ?? null;
        $location->title = $input->title ?? null;
        $location->instanceId = $input->instance_id ?? null;
        $location->country = $input->country ?? 'AU';
        $location->administrativeArea = $input->administrative_area ?? null;
        $location->subAdministrativeArea = $input->sub_administrative_area ?? null;
        $location->locality = $input->locality ?? null;
        $location->dependentLocality = $input->dependent_locality ?? null;
        $location->thoroughfare = $input->thoroughfare ?? null;
        $location->premise = $input->premise ?? null;
        $location->subPremise = $input->sub_premise ?? null;
        $location->organisationName = $input->organisation_name ?? null;
        $location->nameLine = $input->name_line ?? null;
        $location->postalCode = $input->postal_code ?? null;
        $location->authorId = $input->author_id ?? null;
        $location->created = $input->created ?? null;
        $location->updated = $input->updated ?? null;

        return $location;
    }

    public function jsonSerialize()
    {
        $array = [
            'id'                      => $this->id,
            'title'                   => $this->title,
            'instance_id'             => $this->instanceId,
            'country'                 => $this->country,
            'administrative_area'     => $this->administrativeArea,
            'sub_administrative_area' => $this->subAdministrativeArea,
            'locality'                => $this->locality,
            'dependent_locality'      => $this->dependentLocality,
            'thoroughfare'            => $this->thoroughfare,
            'premise'                 => $this->premise,
            'sub_premise'             => $this->subPremise,
            'organisation_name'       => $this->organisationName,
            'name_line'               => $this->nameLine,
            'postal_code'             => $this->postalCode,
            'author_id'               => $this->authorId,
            'created'                 => $this->created,
            'updated'                 => $this->updated,
        ];

        if ($this->original) {
            $array['original'] = $this->original->jsonSerialize();
        }

        return $array;
    }
}
