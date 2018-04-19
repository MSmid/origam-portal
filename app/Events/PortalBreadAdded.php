<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use TCG\Voyager\Models\DataType;
use TCG\Voyager\Events\BreadChanged;

class PortalBreadAdded
{
    use SerializesModels;

    public $dataType;

    public $data;

    public function __construct(DataType $dataType, $data)
    {
        $this->dataType = $dataType;

        $this->data = $data;

        event(new BreadChanged($dataType, $data, 'Added'));
    }
}
