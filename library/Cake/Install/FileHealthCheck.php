<?php
namespace Cake;

class Install_FileHealthCheck extends Install_FileHealthCheckBase
{
    
    public function getFileHashes()
    {
        return array(
        	'library/Cake/addon-Cake.xml' => 'b4748a214a1efa42def45d4b0a6d4b3e',
        	'library/Cake/Admin.php' => 'acfcb19ec9fe6badaa909dc6f76bfa30',
        	'library/Cake/Application.php' => 'fc8f817fc8dd1ad5300f2f4d0a17ff04',
        	'library/Cake/ControllerHelper/Abstract.php' => 'd9a8103c469e6671d39aeb942736a850',
        	'library/Cake/FileHealthCheck.php' => '3db917b3c3a6b0bddf5d51aebf5e08d8',
        	'library/Cake/Helper/Array.php' => '8faf05a03198ed9edf4b04d864d2c045',
        	'library/Cake/Helper/Controller.php' => 'ac77899f696fad60dca5e350f8ea675e',
        	'library/Cake/Helper/DataWriter.php' => '8152b1fdb55e00ff6c004d25a9fff2a2',
        	'library/Cake/Helper/Deferred.php' => 'b036699d7b9e85c71dc8624b37b77e22',
        	'library/Cake/Helper/Model.php' => '5bf9c90c51d62b467c0cb339a1712d06',
        	'library/Cake/Helper/MySql.php' => 'b5299416d4d8a1b88b5e89e1e95a4d80',
        	'library/Cake/Helper/String.php' => '10d9ec73793edaf8fc212e5bbe3b6526',
        	'library/Cake/Helper/Xml.php' => 'f3a2ac470c6ba1b9614422393cfebe73',
        	'library/Cake/Install/Controller.php' => '17dbcfb6429c9515f466f00f338602c8',
        	'library/Cake/Install/Data.php' => 'dcaaec834482cc49017f69e535549dc4',
        	'library/Cake/Install/DataAbstract.php' => '6cd315643f4dc185449457c747e38850',
        	'library/Cake/Install/FileHealthCheckBase.php' => '9434e63988320d93b183ea961aebf495',
        	'library/Cake/Install.php' => '25bf8e05be00c0e58ba2fb8ca35c1610',
        	'library/Cake/Option/DebugOnly.php' => '68d60ed60d61f67c3eda7492bb7e1c2d',
        	'library/Cake/Option/Explain.php' => 'ea5ddf0976ba072587e404fe1ad3b75c',
        	'library/Cake/Proxy/XenForo/ControllerAdmin/AddOn.php' => '692164b04082a9b5b8162a6613634d8b',
        	'library/Cake/Proxy/XenForo/ControllerAdmin/Option.php' => 'cbc5fd6d5132386af7377c20d63d3332',
        	'library/Cake/Proxy/XenForo/DataWriter/AddOn.php' => '0afff65b015619270735821c0e2c11d2',
        	'library/Cake/Proxy/XenForo/DataWriter/AdminTemplate.php' => 'abaa2a78d35f445674df1115bca87dc0',
        	'library/Cake/Proxy/XenForo/DataWriter/Template.php' => '3a95f636211687a7d3463f74db407b66',
        	'library/Cake/Proxy/XenForo/Model/AddOn.php' => '35976d890c0e458e5e43157f53cfb2d1',
        	'library/Cake/Proxy/XenForo/Model/AdminNavigation.php' => 'ba497a97dcef960e659227395f449293',
        	'library/Cake/Proxy/XenForo/Model/AdminTemplate.php' => 'd392b02ee1f808352c04d62fb9d7fdba',
        	'library/Cake/Proxy/XenForo/Model/AdminTemplateModification.php' => 'd8f25e71861e857ddd7af5cd8e06437f',
        	'library/Cake/Proxy/XenForo/Model/BbCode.php' => '4539232ae3332a67d79946ad89a51ec3',
        	'library/Cake/Proxy/XenForo/Model/ContentType.php' => '95ffefaa27ec85fde908ebe9b047c9e0',
        	'library/Cake/Proxy/XenForo/Model/Cron.php' => 'd9760095ebe096977263dfbd77ce3ed0',
        	'library/Cake/Proxy/XenForo/Model/EmailTemplateModification.php' => '32901870caa4d600b799759984550b1e',
        	'library/Cake/Proxy/XenForo/Model/Template.php' => '3e416d61c632e4f1b14e0586a972341a',
        	'library/Cake/Proxy/XenForo/Model/TemplateModification.php' => '95c13696833ec50895e20dfc0f9a8cf9',
        	'library/Cake/Proxy/XenForo/Model/TemplateModificationAbstract.php' => 'd611c634518a3d22295e05f8ba7f5ec5',
        	'library/Cake/Proxy/XenForo/Route/PrefixAdmin/Options.php' => '1d86129f71d2d2d9a2a9b481a9a0d8a4',
        	'library/Cake/Proxy/XenForo/Template/Compiler/Admin.php' => 'f3081a8d68476eda8e0c2bf8bc23b514',
        	'library/Cake/Proxy.php' => '23610124f6ca5e6f0860de1d0a93b5b7',
        	'library/Cake/Template/Compiler/Trait.php' => 'dfa5a3ec95965b702203de6e9f5e957b',
        	'library/Cake/Template/Compiler.php' => '758786bbb25a141bf8c73ac88cbafc14',
        	'library/Cake/Template.php' => '307077936c65a624e835500392fb7ad0',
        	'library/Cake/Trait/Controller.php' => 'a9c4af07d735a54ba4268fcd0db5e770',
        	'library/Cake/Trait/Core.php' => 'e02f41b2a5d7b6e9914fc61401a4e660',
        	'library/Cake/Trait/RoutePrefix.php' => 'b7d3bb10063b3b449868912d45b5a60d',
        	'js/cake/addon_toggle.js' => '25806e8a242dc381107af351ee4d04d4',
        	'js/cake/full/addon_toggle.js' => 'c911bd0bfd4ffe8f8e86999f6b29c2dd',
        	'js/cake/full/index.html' => 'd41d8cd98f00b204e9800998ecf8427e',
        	'js/cake/index.html' => 'd41d8cd98f00b204e9800998ecf8427e',
        	'styles/default/cake/index.html' => 'd41d8cd98f00b204e9800998ecf8427e',
        );
    }
}