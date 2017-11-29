<?php

namespace Netgen\Bundle\ContentfulBlockManagerBundle\Controller;

use Exception;
use Contentful\Delivery\DynamicEntry;
use Contentful\Delivery\Synchronization\DeletedEntry;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
* A custom controller to handle a content specified by a route.
*/
class ContentfulController extends Controller
{
    /*
     * Contentful topic constants (sent as X-Contentful-Topic header)
     */
    const ENTRY_PUBLISH = "ContentManagement.Entry.publish";
    const ENTRY_UNPUBLISH = "ContentManagement.Entry.unpublish";
    const ENTRY_DELETE = "ContentManagement.Entry.delete";
    const ENTRY_ARCHIVE = "ContentManagement.Entry.archive";
    const ENTRY_UNARCHIVE = "ContentManagement.Entry.unarchive";
    const ENTRY_CREATE = "ContentManagement.Entry.create";
    
    const CONTENT_TYPE_PUBLISH = "ContentManagement.ContentType.publish";
    const CONTENT_TYPE_UNPUBLISH = "ContentManagement.ContentType.unpublish";
    const CONTENT_TYPE_DELETE = "ContentManagement.ContentType.delete";
    const CONTENT_TYPE_CREATE = "ContentManagement.ContentType.create";

    /**
    * @param object $contentDocument the name of this parameter is defined
    *      by the RoutingBundle. You can also expect any route parameters
    *      or $template if you configured templates_by_class (see below).
    *
    * @return Response
    */
    public function viewAction($contentDocument)
    {
        if (!$contentDocument->getIsPublished() or $contentDocument->getIsDeleted())
            throw new NotFoundHttpException('Not found.');

        return $this->render('@NetgenContentfulBlockManager/contentful/content.html.twig', [
            'content' => $contentDocument,
        ]);
    }

    /**
     * Contentful webhook for clearing local caches
     */
    public function webhookAction(Request $request)
    {
        /**
         * @var \Netgen\Bundle\ContentfulBlockManagerBundle\Service\Contentful $service
         */
        $service = $this->container->get("netgen_block_manager.contentful.service");
        $content = $request->getContent();
        $spaceId = $request->headers->get("X-Space-Id");

        try {
            /**
             * @var \Contentful\Delivery\Client @client
             */
            $client = $service->getClientBySpaceId($spaceId);
            $remote_entry = $client->reviveJson($content);

        } catch (Exception $e) {
            throw new BadRequestHttpException("Invalid request");
        }

        switch ($request->headers->get("X-Contentful-Topic")) {
            case $this::ENTRY_CREATE:
            case $this::ENTRY_PUBLISH:
            case $this::ENTRY_UNARCHIVE:
                if (! $remote_entry instanceof DynamicEntry)
                    throw new BadRequestHttpException("Invalid request");
                $service->refreshContentfulEntry($remote_entry);
                break;
            case $this::ENTRY_UNPUBLISH:
            case $this::ENTRY_ARCHIVE:    
                if (! $remote_entry instanceof DeletedEntry)
                    throw new BadRequestHttpException("Invalid request");
                $service->unpublishContentfulEntry($remote_entry);
                break;
            case $this::ENTRY_DELETE:
                if (! $remote_entry instanceof DeletedEntry)
                    throw new BadRequestHttpException("Invalid request");
                $service->deleteContentfulEntry($remote_entry);
                break;
            case $this::CONTENT_TYPE_CREATE:
            case $this::CONTENT_TYPE_PUBLISH:
            case $this::CONTENT_TYPE_UNPUBLISH:
            case $this::CONTENT_TYPE_DELETE:
                $service->refreshContentTypeCache($client, new Filesystem());
                break;
            default:
                throw new BadRequestHttpException("Invalid request");
        }

        return new Response("OK",Response::HTTP_OK);
    }
}
