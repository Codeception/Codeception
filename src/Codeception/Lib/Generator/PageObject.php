<?php
namespace Codeception\Lib\Generator;

use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class PageObject
{
    use Namespaces;
    use Shared\Classname;

    protected $template  = <<<EOF
<?php
{{namespace}}
class {{class}}Page
{
    // include url of current page
    public static \$URL = '';

    /**
     * Declare UI map for this page here. CSS or XPath allowed.
     * public static \$usernameField = '#username';
     * public static \$formSubmitButton = "#mainForm input[type=submit]";
     */

    /**
     * Basic route example for your current URL
     * You can append any additional parameter to URL
     * and use it in tests like: EditPage::route('/123-post');
     */
    public static function route(\$param)
    {
        return static::\$URL.\$param;
    }

{{actions}}
}
EOF;

    protected $actionsTemplate  = <<<EOF
    /**
     * @var {{actorClass}};
     */
    protected \${{actor}};

    public function __construct({{actorClass}} \$I)
    {
        \$this->{{actor}} = \$I;
    }

    /**
     * @return {{pageObject}}Page
     */
    public static function of({{actorClass}} \$I)
    {
        return new static(\$I);
    }
EOF;

    protected $actions = '';
    protected $settings;
    protected $name;

    public function __construct($settings, $name)
    {
        $this->settings = $settings;
        $this->name = $this->getShortClassName($this->removeSuffix($name, 'Page'));
    }

    public function produce()
    {
        $ns = $this->getNamespaceString($this->settings['namespace'].'\\'.$this->name);
        
        return (new Template($this->template))
            ->place('namespace', $ns)
            ->place('actions', $this->produceActions())
            ->place('class', $this->name)
            ->produce();
    }

    protected function produceActions()
    {
        if (!isset($this->settings['class_name'])) {
            return ''; // global pageobject
        }

        $actorClass = $this->settings['class_name'];
        $actor = lcfirst($this->settings['class_name']);

        return (new Template($this->actionsTemplate))
            ->place('actorClass', $actorClass)
            ->place('actor', $actor)
            ->place('pageObject', $this->name)
            ->produce();
    }

}
