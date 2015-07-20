<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 8: Pets     g_initPath()
//  tabid 0: Database g_initHeader()
class EmotePage extends GenericPage
{
    use DetailPage;

    protected $type          = TYPE_PET;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 100];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        $this->typeId = intVal($id);

        $this->subject = new EmoteList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Util::ucFirst(Lang::game('emote')), Lang::emote('notFound'));

        $this->name = Util::ucFirst($this->subject->getField('cmd'));
    }

    protected function generatePath() { }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::game('emote')));
    }

    protected function generateContent()
    {
        /***********/
        /* Infobox */
        /***********/

        $infobox = [];

        // has Animation
        if ($this->subject->getField('isAnimated'))
            $infobox[] = Lang::emote('isAnimated');

        /****************/
        /* Main Content */
        /****************/

        $text = '';
        if ($aliasses = DB::Aowow()->selectCol('SELECT command FROM ?_emotes_aliasses WHERE id = ?d AND locales & ?d', $this->typeId, 1 << User::$localeId))
        {
            $text .= '[h3]'.Lang::emote('aliases').'[/h3][ul]';
            foreach ($aliasses as $a)
                $text .= '[li]/'.$a.'[/li]';

            $text .= '[/ul][br][br]';
        }

        $texts = [];
        if ($_ = $this->subject->getField('self', true))
            $texts[Lang::emote('self')] = $_;

        if ($_ = $this->subject->getField('target', true))
            $texts[Lang::emote('target')] = $_;

        if ($_ = $this->subject->getField('noTarget', true))
            $texts[Lang::emote('noTarget')] = $_;

        if (!$texts)
            $text .= '[div][i class=q0]'.Lang::emote('noText').'[/i][/div]';
        else
            foreach ($texts as $h => $t)
                $text .= '[pad][b]'.$h.'[/b][ul][li][span class=s4]'.preg_replace('/%\d?\$?s/', '<'.Util::ucFirst(Lang::main('name')).'>', $t).'[/span][/li][/ul]';

        $this->extraText = $text;
        $this->infobox   = '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]';

        /**************/
        /* Extra Tabs */
        /**************/

        // tab: achievement
        $condition = array(
            ['ac.type', ACHIEVEMENT_CRITERIA_TYPE_DO_EMOTE],
            ['ac.value1', $this->typeId],
        );
        $acv = new AchievementList($condition);

        $this->lvTabs[] = array(
            'file'   => 'achievement',
            'data'   => $acv->getListviewData(),
            'params' => []
        );

        $this->extendGlobalData($acv->getJsGlobals());
    }
}

?>