<?php
namespace Codeception\Lib\Generator;

use Codeception\Util\Template;

class PageObject
{
    use Shared\Namespaces;
    use Shared\Classname;

    protected $template  = <<<EOF
<?php
{{namespace}}
class {{class}}Page
{
    // include url of current page
    static \$URL = '';

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
     * @var {{guyClass}};
     */
    protected \${{guy}};

    public function __construct({{guyClass}} \$I)
    {
        \$this->{{guy}} = \$I;
    }

    /**
     * @return {{pageObject}}Page
     */
    public static function of({{guyClass}} \$I)
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
            return ""; // global pageobject
        }

        $guyClass = $this->settings['class_name'];
        $guy = lcfirst($this->settings['class_name']);

        return (new Template($this->actionsTemplate))
            ->place('guyClass', $guyClass)
            ->place('guy', $guy)
            ->place('pageObject', $this->name)
            ->produce();
    }


} 