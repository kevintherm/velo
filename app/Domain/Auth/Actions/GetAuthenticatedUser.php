<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Collection\Enums\CollectionType;
use App\Domain\Collection\Models\Collection;
use App\Domain\Record\Models\Record;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class GetAuthenticatedUser
{
    public function execute(Request $request, Collection $collection): Record
    {
        if ($collection->type !== CollectionType::Auth) {
            throw new BadRequestHttpException('Collection is not auth enabled.');
        }

        $session = $request->user();
        if (! $session || ! $session->meta?->_id) {
            throw new UnauthorizedHttpException('Unauthorized.');
        }

        $record = Record::find($session->meta?->_id);
        if (! $record) {
            throw new NotFoundHttpException('User not found.');
        }

        return $record;
    }
}
