<?php

class AuthLib {

    function AuthLib() {
        $this->recursos_publicos = array(
            '?d=seguranca&a=auth&f=logon',
            '?d=seguranca&a=auth&f=logout',
            '?d=seguranca&a=auth&f=login',
            '?d=seguranca&a=auth&f=esqueciSenha',
            '?d=seguranca&a=recursos&f=gerarModels',
            '?d=portal&a=portal&f=index');
    }

    function ehRecursoPublico($url = null) {
        return in_array($url, $this->recursos_publicos);
    }

    function estaLogado() {
        return isset($_SESSION['credencial']['uuid']);
    }

    function temAutorizacao($url = null) {
        return true;
        /*
        if (isset($_SESSION['credencial']['recursos'][$url])) {
            return $_SESSION['credencial']['recursos'][$url];
        } else {
            return false;
        }*/
    }
    function pertence_a($grupo = 'Coordenação') {
        include_once ("models/AuthModel.php");
        $auth = &singleton('AuthModel');
        return $auth->pertence_a($grupo);
    }

    function perfil_completo() {
        return $_SESSION['credencial']['perfil_completo'];
    }

    function logout() {
        session_unset();
        session_destroy();
    }

}