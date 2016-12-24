<?php

namespace Kss\Bundle\BridgeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Kss\Parser;

class ExampleController extends Controller
{
    /**
     * @Route("/")
     * @Template
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/{reference}/{title}", requirements={"reference" = "(\d+\.?)+"})
     * @Template
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function referenceAction($reference, $title = null)
    {
        $parser = $this->getKssParser();
        $section = $parser->getSection($reference);
        if (!$section->getReference()) {
            throw $this->createNotFoundException('Reference ' . $reference . ' does not exist in the Styleguide!');
        }
        $sections = $parser->getSectionChildren($section->getReference());

        return array(
            'section' => $section,
            'sections' => $sections
        );
    }

    /**
     * @Route("/fragment/menu")
     * @Template
     */
    public function menuAction()
    {
        $links = array();
        $links[] = array(
            'name' => 'Home',
            'title' => '',
            'url' => $this->generateUrl('kss_bridge_example_index'),
        );

        $parser = $this->getKssParser();
        $sections = $parser->getTopLevelSections();
        foreach ($sections as $section) {
            $link = array(
                'name' => $section->getTitle(),
                'title' => $section->getDescription(),
                'url' => $this->generateUrl(
                    'kss_bridge_example_reference',
                    array(
                        'reference' => $section->getReference(),
                        'title' => strtolower(preg_replace('/\W+/', '-', $section->getTitle())),
                    )
                ),
            );
            $links[] = $link;
        }

        return array(
            'links' => $links,
        );
    }

    /**
     * Returns a KSS Parser loaded with the CSS files from the bundle
     *
     * @return Parser
     * @throws \InvalidArgumentException
     */
    protected function getKssParser()
    {
        return new Parser(__DIR__ . '/../Resources/public/css');
    }
}
