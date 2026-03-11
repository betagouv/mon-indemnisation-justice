<?php

namespace MonIndemnisationJustice\Api\Requerant\Moi;

use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\UsagerDto;

class MoiDto
{
    public UsagerDto $dto;
    public ?string $incarnePar = null;
}
