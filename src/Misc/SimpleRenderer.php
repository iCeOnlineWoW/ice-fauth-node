<?php

/**
 * Very simple renderer wrapper
 * Since we use templating in just a few limited scenarios, we don't use any templating engine
 * to avoid initialization of gigantic engines just for a few strings
 */
class SimpleRenderer
{
    /** @var string */
    private $pageName;
    /** @var string */
    private $lang;

    public function __construct($name, $language = 'en')
    {
        $this->pageName = $name;
        $this->lang = $language;
    }

    /**
     * Renders page, substituting given strings in output
     * @param array $strings
     * @return string
     * @throws Exception
     */
    public function render($strings = []): string
    {
        // verify existence of language file
        if (!file_exists(__DIR__."/../Templates/Lang/".$this->pageName.".".$this->lang.".php"))
            $this->lang = "en";

        // if it exists, use it
        if (file_exists(__DIR__."/../Templates/Lang/".$this->pageName.".".$this->lang.".php"))
            $langStrings = require __DIR__."/../Templates/Lang/".$this->pageName.".".$this->lang.".php";
        else // if not, no translation
            $langStrings = [];

        $pageContents = file_get_contents(__DIR__."/../Templates/".$this->pageName.".html");
        if (strlen($pageContents) === 0)
            throw new Exception("No such template file: ".$this->pageName);

        // substitute requested strings
        foreach ($strings as $id => $loc)
        {
            if (is_string($loc))
                $pageContents = str_replace("{{".$id."}}", $loc, $pageContents);
            else if (get_class($loc) === 'TranslationWrapper')
            {
                $dst = isset($langStrings[$loc->string]) ? $langStrings[$loc->string] : $loc->string;
                $pageContents = str_replace("{{".$id."}}", $dst, $pageContents);
            }
                
        }
        // substitute language strings
        foreach ($langStrings as $id => $loc)
            $pageContents = str_replace("{{LANG:$id}}", $loc, $pageContents);

        return $pageContents;
    }

    /**
     * Renders page, substituting given strings in output, wrapping output into a stream
     * @param array $strings
     * @return \Slim\Http\Body
     */
    public function renderStream($strings = []): \Slim\Http\Body
    {
        $stream = fopen('php://memory','r+');
        fwrite($stream, $this->render($strings));
        rewind($stream);
        return new \Slim\Http\Body($stream);
    }
}
