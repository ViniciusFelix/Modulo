<?php
function arrayToUL(array $array) {
    echo '<ul>';
    foreach ($array as $key => $value) {
        if (isset($value['name']))
            echo "<li>{$value['name']}</li>";
        if (!empty($value['children']) && is_array($value['children'])) {
            echo arrayToUL($value['children']);
        }
    }
    echo '</ul>';
}
function renderTreeXML($arvore) {
    $xml = '';
    $xml .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    $xml .= "<root>\n";
    foreach ($arvore as $node) {
        if ($node['level'] == 0) {
            $format = "\t<item id=\"tree_node[%s]\" parent_id=\"0\">\n";
            $format .= "\t\t<content><name><![CDATA[%s]]></name></content>\n";
            $xml .= sprintf($format, $node->getNode()->getRecord()->uuid, $node->getNode()->getRecord()->nome);
            $xml .= "\t</item>\n";
        } else {
            $format = "\t<item id=\"tree_node[%s]\" parent_id=\"tree_node[%s]\">\n";
            $format .= "\t\t<content><name><![CDATA[%s]]></name></content>\n";
            $xml .= sprintf($format, $node->getNode()->getRecord()->uuid, $node->getNode()->getParent()->uuid, $node->getNode()->getRecord()->nome);
            $xml .= "\t</item>\n";
        }
    }
    $xml .= "</root>";
    return $xml;
}
function renderTree($arvore) {

//Here we store the level of the last item we printed out
    $lastLevel = 0;
//Outer list item
    $html = "<ul class=\"jstree\">";
//Iterating tree from tree root
    foreach ($arvore->fetchTree() as $node) {
//If we are on the item of the same level, closing <li> tag before printing item
        if (($node['level'] == $lastLevel) and ($lastLevel > 0)) {
            $html .= '</li>';
        }
//If we are printing a next-level item, starting a new <ul>
        if ($node['level'] > $lastLevel) {
            $html .= '<ul>';
        }
//If we are going to return back by several levels, closing appropriate tags
        if ($node['level'] < $lastLevel) {
            $html .= str_repeat("</li></ul>", $lastLevel - $node['level']) . '</li>';
        }
//Priting item without closing tag
        $html .= '
            <li id="tree_node[' . $node['uuid'] . ']">
            <ins class="jstree-icon">&nbsp;</ins>
            <a><ins class="jstree-icon">&#160;</ins>' . $node['nome'] . '</a>';
//Refreshing last level of the item
        $lastLevel = $node['level'];
    }
    $html .= "</ul>";
    return $html;
}

function renderMenu($arvore) {
    $retorno = '';
    try {
//Here we store the level of the last item we printed out
        $lastLevel = 0;
//Outer list item
        $html = "<ul class=\"sf-menu\">";
//Iterating tree from tree root
        foreach ($arvore->fetchTree() as $node) {
//If we are on the item of the same level, closing <li> tag before printing item
            if (($node['level'] == $lastLevel) and ($lastLevel > 0)) {
                $html .= '</li>';
            }
//If we are printing a next-level item, starting a new <ul>
            if ($node['level'] > $lastLevel) {
                $html .= '<ul>';
            }
//If we are going to return back by several levels, closing appropriate tags
            if ($node['level'] < $lastLevel) {
                $html .= str_repeat("</li></ul>", $lastLevel - $node['level']) . '</li>';
            }
//Priting item without closing tag
            $html .= '
            <li><a href="' . $node['url'] . '" target="_self">' . $node['nome'] . '</a>';
//Refreshing last level of the item
            $lastLevel = $node['level'];
        }
        $html .= "</ul>";
        $arvore->resetBaseQuery();
        return $html;
    } catch (Exception $e) {
        $html = "<ul class=\"sf-menu\">";
        $html .= '<li><a href="?d=index&a=index&f=principal" target="_self">Início</a>';
        $html .= "</ul>";
        return $html;
    }
}

function renderTreeOrgaos($arvore) {
//Here we store the level of the last item we printed out
    $lastLevel = 1;
//Outer list item
    $html = "<ul class=\"jstree\">";
//Iterating tree from tree root
    foreach ($arvore as $node) {
//If we are on the item of the same level, closing <li> tag before printing item
        if (($node['level'] == $lastLevel) and ($lastLevel > 0)) {
            $html .= '</li>';
        }
//If we are printing a next-level item, starting a new <ul>
        if ($node['level'] > $lastLevel) {
            $html .= '<ul>';
        }
//If we are going to return back by several levels, closing appropriate tags
        if ($node['level'] < $lastLevel) {
            $html .= str_repeat("</li></ul>", $lastLevel - $node['level']) . '</li>';
        }
//Priting item without closing tag
        $html .= '
            <li id="tree_node[' . $node['uuid'] . ']">
            <ins class="jstree-icon">&nbsp;</ins>
            <a><ins class="jstree-icon">&#160;</ins>' . $node['nome'] . '</a>';
//Refreshing last level of the item
        $lastLevel = $node['level'];
    }
    $html .= "</ul>";
    return $html;
}

?>
