<?php

/**
 * 指定したセルの文字列を取得する
 *
 * 色づけされたセルなどは cell->getValue()で文字列のみが取得できない
 * また、複数の配列に文字列データが分割されてしまうので、その部分も連結して返す
 *
 *
 * @param  $objCell Cellオブジェクト
 */
function getCellText($objCell = null)
{
     if (is_null($objCell)) {
         return false;
     }

     $txtCell = "";

     //まずはgetValue()を実行
     $valueCell = $objCell->getValue();

     if (is_object($valueCell)) {
         //オブジェクトが返ってきたら、リッチテキスト要素を取得
         $rtfCell = $valueCell->getRichTextElements();
         //配列で返ってくるので、そこからさらに文字列を抽出
         $txtParts = array();
         foreach ($rtfCell as $v) {
            $txtParts[] = $v->getText();
         }
         //連結する
         $txtCell = implode("", $txtParts);

     } else {
         if (!empty($valueCell)) {
             $txtCell = $valueCell;
         }
     }

     return $txtCell;
}
