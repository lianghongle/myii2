<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace strong\debug\panels;

use Yii;
use yii\debug\Panel;

/**
 * Debugger panel that collects and displays application configuration and environment.
 *
 * @property array $extensions This property is read-only.
 * @property array $phpInfo This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class OtherPanel extends Panel
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'other';
    }

    /**
     * @inheritdoc
     */
    public function getSummary()
    {
        $url = $this->getUrl();
        $name = $this->getName();
        return <<<EOD
<div class="yii-debug-toolbar__block">
    <a href="$url">
        $name
    </a>
</div>
EOD;
    }

    /**
     * @inheritdoc
     */
    public function getDetail()
    {
        $table = '';
        if(Yii::$app->has('xhprof') && Yii::$app->xhprof->isEnable){
            $xhprofUrl = Yii::$app->xhprof->displayUrl . '?run=' . $this->data['xhprof']['run'] . '&source=' . $this->data['xhprof']['source'];
            $table .= "<tr><th>xhprof</th><td><a href=\"$xhprofUrl\" target=\"_blank\">查看</a></td></tr>";
        }


        return <<<EOD
    <h1>Other</h1>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <caption><p></p></caption>
            <tbody>{$table}</tbody>
        </table>
    </div>
EOD;
    }

    public function save()
    {
        $data = [];

        //xhprof
        if(Yii::$app->has('xhprof') && Yii::$app->xhprof->isEnable){
            $data['xhprof'] = [
                'source' => Yii::$app->xhprof->source,
                'run' => Yii::$app->xhprof->run,
            ];
        }

        return $data;
    }
}
