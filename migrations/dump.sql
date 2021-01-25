-- Adminer 4.3.0 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `tempresas`;
CREATE TABLE `tempresas` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador',
  `razao_social` varchar(200) NOT NULL COMMENT 'Razão Social',
  `nome_fantasia` varchar(200) NOT NULL COMMENT 'Nome Fantasia. Utilizado também no titulo das páginas',
  `dominio` varchar(200) NOT NULL COMMENT 'Domínio do sistema',
  `diretorio` varchar(90) DEFAULT NULL,
  `situacao` enum('ativo','inativo') NOT NULL DEFAULT 'ativo' COMMENT 'Situação',
  `cor_principal` varchar(7) DEFAULT NULL COMMENT 'Cor principal para o menu',
  `api_token` varchar(48) DEFAULT NULL,
  `oracle_host` varchar(90) DEFAULT NULL,
  `oracle_porta` int(6) DEFAULT NULL,
  `oracle_sid` varchar(90) DEFAULT NULL,
  `oracle_usuario` varchar(90) DEFAULT NULL,
  `oracle_senha` varchar(90) DEFAULT NULL,
  `empr_id` int(11) NOT NULL,
  `empr_nfe` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tempresas` (`id`, `razao_social`, `nome_fantasia`, `dominio`, `diretorio`, `situacao`, `cor_principal`, `api_token`, `oracle_host`, `oracle_porta`, `oracle_sid`, `oracle_usuario`, `oracle_senha`, `empr_id`, `empr_nfe`) VALUES
(1, 'Default',  'Default',  'portal-default.lidere',  'default',  'ativo',    '#b13125',  'L726PT9LMVQEZJCKN2O382753NBX', '192.168.0.1', 1521,   'LIDERE', 'FOCCO3I',  'FOCCO3I',  1,  1);

DROP TABLE IF EXISTS `tempresa_parametros`;
CREATE TABLE `tempresa_parametros` (
  `parametro_id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `valor` varchar(300) DEFAULT NULL COMMENT 'Valor do parâmetro para a empresa',
  `data_edicao` datetime DEFAULT NULL,
  PRIMARY KEY (`parametro_id`,`empresa_id`),
  KEY `fk_tparametros_has_tempresas_tempresas1_idx` (`empresa_id`),
  KEY `fk_tparametros_has_tempresas_tparametros1_idx` (`parametro_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tempresa_parametros` (`parametro_id`, `empresa_id`, `valor`, `data_edicao`) VALUES
(6, 1,  'smtp.testes.com.br',   '2017-10-17 09:41:44'),
(7, 1,  NULL,   NULL),
(15,    1,  NULL,   '2017-09-25 10:39:09');

DROP TABLE IF EXISTS `tenvio_emails`;
CREATE TABLE `tenvio_emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `tipo` enum('portal','sac') NOT NULL DEFAULT 'portal',
  `data_criacao` datetime NOT NULL COMMENT 'Data de criação',
  `empresa_id` int(11) NOT NULL COMMENT 'Identificador da empresa',
  `titulo` varchar(300) NOT NULL COMMENT 'Título do e-mail',
  `conteudo` text NOT NULL COMMENT 'Conteúdo do e-mail',
  `destinatario` text NOT NULL COMMENT 'Destinatário do e-mail',
  `destinatario_oculto` text COMMENT 'Destinatário oculto do e-mail',
  `arquivos` text COMMENT 'Arquivos em anexo',
  `enviado` datetime DEFAULT NULL COMMENT 'Data do envio',
  PRIMARY KEY (`id`),
  KEY `fk_tenvio_emails_tempresas` (`empresa_id`),
  CONSTRAINT `tenvio_emails_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `tempresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `tlogs`;
CREATE TABLE `tlogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `icon` varchar(50) DEFAULT NULL COMMENT 'Icone para mostrar na tela',
  `data` datetime NOT NULL COMMENT 'Data',
  `tipo` varchar(45) NOT NULL COMMENT 'Tipo do log. Campo aberto para possibilitar a criação de novos tipos, sem precisar alterar banco',
  `log` text COMMENT 'Descrição do log',
  `usuario_id` int(11) DEFAULT NULL COMMENT 'ID do usuário. É opcional pois os logs de tentativa de acesso também são capturados',
  `empresa_id` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`,`empresa_id`),
  KEY `fk_tlogs_tempresas_idx` (`empresa_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `tmodulos`;
CREATE TABLE `tmodulos` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador',
  `nome` varchar(90) NOT NULL COMMENT 'Nome',
  `url` varchar(90) DEFAULT '' COMMENT 'URL',
  `ordem` int(11) DEFAULT NULL COMMENT 'Ordenação',
  `modulo_id` int(11) DEFAULT NULL,
  `icone` varchar(100) DEFAULT NULL COMMENT 'Ícone no menu',
  `menu` enum('S','N') DEFAULT 'S' COMMENT 'Exibe o modulo no menu',
  PRIMARY KEY (`id`),
  KEY `fk_tmodulos_tmodulos1_idx` (`modulo_id`),
  CONSTRAINT `tmodulos_ibfk_1` FOREIGN KEY (`modulo_id`) REFERENCES `tmodulos` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tmodulos` (`id`, `nome`, `url`, `ordem`, `modulo_id`, `icone`, `menu`) VALUES
(1, 'Auxiliares',   '#auxiliares',  1000,   NULL,   'icon mdi-action-settings i-20',    'S'),
(2, 'Usuários', 'auxiliares/usuarios',  310,    1,  '', 'S'),
(3, 'Parâmetros',   'auxiliares/parametros',    310,    1,  '', 'S'),
(4, 'Logs', 'logs', 100,    NULL,   'icon mdi-action-assignment-late i-20', 'S'),
(5, 'Dashboard',    'home', 0,  NULL,   'icon mdi-device-dvr i-20', 'S'),
(6, 'Login',    'login',    NULL,   NULL,   NULL,   'N'),
(7, 'Tarefas (Jobs)',   'auxiliares/tasks', 1001,   1,  NULL,   'S'),
(8, 'PHP Info', 'php/info', 1002,   1,  NULL,   'S'),
(9, 'App Pedido de Venda - Cores',  'apppedidovenda/cores', 1002,   1,  NULL,   'S');

DROP TABLE IF EXISTS `tmodulos_usuarios`;
CREATE TABLE `tmodulos_usuarios` (
  `modulo_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `empresa_empr_id` int(11) NOT NULL COMMENT 'empresa id',
  `permissao` int(2) DEFAULT NULL,
  PRIMARY KEY (`modulo_id`,`usuario_id`,`empresa_empr_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `tmodulos_usuarios_ibfk_1` FOREIGN KEY (`modulo_id`) REFERENCES `tmodulos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tmodulos_usuarios_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `tusuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tmodulos_usuarios` (`modulo_id`, `usuario_id`, `empresa_empr_id`, `permissao`) VALUES
(1, 2,  1,  1),
(3, 2,  1,  2),
(4, 2,  1,  3),
(4, 3,  1,  1),
(5, 2,  1,  1),
(5, 3,  1,  3),
(6, 2,  1,  3),
(6, 3,  1,  3);

DROP TABLE IF EXISTS `tparametros`;
CREATE TABLE `tparametros` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador',
  `grupo` varchar(100) NOT NULL,
  `parametro` varchar(90) NOT NULL COMMENT 'Parâmetro',
  `descricao` varchar(300) DEFAULT NULL COMMENT 'Descrição do parâmetro. Utilizado para o help.',
  `sistema` int(1) NOT NULL DEFAULT '0' COMMENT 'Parâmetro escondido do sistema',
  `esconde` int(1) NOT NULL DEFAULT '0' COMMENT 'Se for senha, marca como 1 a aplicação esconde',
  `tipo` enum('input','text','radio','checkbox','select') NOT NULL DEFAULT 'input' COMMENT 'Controla o tipo de campo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tparametros` (`id`, `grupo`, `parametro`, `descricao`, `sistema`, `esconde`, `tipo`) VALUES
(6, 'Portal',   'portal_smtp_host', 'Portal - Endereço do servidor de e-mails', 0,  0,  'input'),
(7, 'Portal',   'portal_smtp_porta',    'Portal - Porta do servidor de e-mails',    0,  0,  'input'),
(8, 'Portal',   'portal_smtp_usuario',  'Portal - E-mail responsável pelo envio',   0,  0,  'input'),
(9, 'Portal',   'portal_smtp_senha',    'Portal - Senha do e-mail', 0,  1,  'input'),
(10,    'Portal',   'portal_smtp_nome', 'Portal - Nome que aparece no e-mail',  0,  0,  'input'),
(15,    'Portal',   'portal_texto_email_nova_senha',    'Portal - Texto do e-mail de solicitação de senha', 0,  0,  'input'),
(16,    'Portal',   'portal_exibe_usuario_erp', 'Portal - Exibe o campo de seleção de usuários do ERP', 0,  0,  'radio'),
(17,    'Portal',   'portal_exibe_select_cliente_erp',  'Portal - Exibe o campo de seleção de clientes do ERP', 0,  0,  'radio');

DROP TABLE IF EXISTS `tsequencias`;
CREATE TABLE `tsequencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(500) NOT NULL,
  `numero_seq` int(11) NOT NULL,
  `empresa_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tsequencias` (`id`, `descricao`, `numero_seq`, `empresa_id`) VALUES
(1, 'nr_seq_padrao_rateio', 140,    NULL),
(2, 'nr_seq_dre',   35, NULL);

DROP TABLE IF EXISTS `ttasks`;
CREATE TABLE `ttasks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `minute` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT '*',
  `hour` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT '*',
  `day` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT '*',
  `month` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT '*',
  `weekday` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT '*',
  `commonOptions` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '* * * * *',
  `job` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `running_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `ttasks` (`id`, `minute`, `hour`, `day`, `month`, `weekday`, `commonOptions`, `job`, `description`, `running_at`, `created_at`, `updated_at`) VALUES
(2, '*/2',  '*',    '*',    '*',    '*',    '*/2 * * * *',  'worker/envia-emails',  'Cron para envio de email', '2017-10-26 17:54:13',  '2017-10-26 19:54:13',  '2017-10-26 17:54:13'),
(4, '*',    '*',    '*',    '*',    '*',    '---',  'worker/env',   'Cria um arquivo para verificar se o cron esta rodando',    '2017-10-26 17:54:13',  '2017-10-26 19:54:13',  '2017-10-26 17:54:13');

DROP TABLE IF EXISTS `tusuarios`;
CREATE TABLE `tusuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `empresa_id` int(11) DEFAULT NULL COMMENT 'Identificador da empresa',
  `tipo` varchar(10) DEFAULT 'user' COMMENT 'Se admin o usuário tem permissão de administrador',
  `nome` varchar(300) NOT NULL COMMENT 'Nome do Usuário',
  `usuario` varchar(90) NOT NULL COMMENT 'Login utilizado para acessar o sistema',
  `senha` varchar(300) DEFAULT NULL COMMENT 'Senha',
  `email` varchar(200) NOT NULL COMMENT 'E-mail do usuário',
  `situacao` enum('ativo','inativo') NOT NULL DEFAULT 'ativo' COMMENT 'Situação que bloqueia o acesso ao sistema',
  `data_criacao` datetime DEFAULT NULL COMMENT 'Data de criação',
  `data_edicao` datetime DEFAULT NULL COMMENT 'Data da última alteração',
  `sistema` int(1) NOT NULL DEFAULT '0' COMMENT 'Usuário é default do sistema (Lidere) e não testa módulos (Acessa TODOS os módulos cadastrados)',
  `ad` tinyint(1) DEFAULT '0' COMMENT 'Se 1 - loga pelo ad se zero login normal',
  `token` varchar(50) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_UNIQUE` (`usuario`),
  KEY `fk_tusuarios_tempresas` (`empresa_id`),
  CONSTRAINT `fk_tusuarios_tempresas` FOREIGN KEY (`empresa_id`) REFERENCES `tempresas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tusuarios` (`id`, `empresa_id`, `tipo`, `nome`, `usuario`, `senha`, `email`, `situacao`, `data_criacao`, `data_edicao`, `sistema`, `ad`, `token`) VALUES
(1, 1,  'admin',    'Lidere Sistemas',    'lidere', '$6$rounds=5000$5a70d4eaa4c36$cMdMTA8P3sSFkSuyPK3lKcPXUoNw0dloyI/ScFxq2xLTO5SqaMkho115W.jzLZhIyzucIkWou3rogNhijJOHN.',  'marcel@lideresistemas.com.br',   'ativo',    '2016-06-14 10:05:01',  '2016-12-09 16:19:04',  1,  0,  'Aw9nCpSkndgJIXtjpXsV'),
(2, 1,  'user', 'Ramon Barros', 'ramon',    '$6$rounds=5000$59e5ea5a8004c$tUX77o2RrNc33qTtvZjZokOLF.OBBn.WC3Y9PXh3vdkxkPMmu20Hfgml4ZgqH49AlVtMQBFOuRjYQ.Y6H3FhT1',  'ramon@lideresistemas.com.br',    'ativo',    '2017-08-02 00:13:36',  '2017-10-23 15:20:05',  0,  0,  ''),
(3, 1,  'user', 'teste',    'teste',    '$6$rounds=5000$59ee25dda1ff8$AmhcbbMVFO/Wp163.x4TdDwgvJIgB0xPxzju9In.76UPPVqwGIHkrZjfFraAYAXN8WLxr8ZRDWn/Q4PQRB7o70',  'contato@ramon-barros.com', 'ativo',    '2017-10-23 15:20:24',  '2017-10-23 15:32:26',  0,  0,  '');

DROP TABLE IF EXISTS `tusuarios_clientes`;
CREATE TABLE `tusuarios_clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `cliente_erp_id` int(11) DEFAULT NULL,
  `cliente_erp_cod_cli` int(11) DEFAULT NULL,
  `cliente_erp_descricao` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `tusuarios_clientes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `tusuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2018-01-30 20:22:06
