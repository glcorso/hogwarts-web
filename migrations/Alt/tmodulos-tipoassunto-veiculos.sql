-- Adminer 4.7.5 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

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
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8;

INSERT INTO `tmodulos` (`id`, `nome`, `url`, `ordem`, `modulo_id`, `icone`, `menu`) VALUES
(1,	'Auxiliares',	'#auxiliares',	1000,	NULL,	'mdi mdi-hexagon-multiple',	'S'),
(2,	'Usuários',	'auxiliares/usuarios',	310,	1,	'',	'S'),
(3,	'Parâmetros',	'auxiliares/parametros',	310,	1,	'',	'S'),
(4,	'Logs',	'logs',	100,	NULL,	'mdi mdi-alert-circle',	'S'),
(5,	'Dashboard',	'home',	0,	NULL,	'mdi mdi-gauge',	'S'),
(6,	'Login',	'login',	NULL,	NULL,	NULL,	'N'),
(7,	'Tarefas (Jobs)',	'auxiliares/tasks',	1001,	1,	NULL,	'S'),
(8,	'PHP Info',	'php/info',	1002,	1,	NULL,	'S'),
(9,	'Empresas',	'auxiliares/empresas',	10,	1,	NULL,	'S'),
(10,	'Assistência Técnica',	'#assistencia-tecnica',	10,	NULL,	'ti-headphone-alt',	'S'),
(11,	'Atendimento',	'assistencia-tecnica/atendimento',	10,	10,	NULL,	'S'),
(12,	'Motivos',	'assistencia-tecnica/motivos',	40,	10,	NULL,	'S'),
(14,	'Consulta',	'assistencia-tecnica/consulta',	20,	10,	NULL,	'S'),
(15,	'Defeitos',	'assistencia-tecnica/defeitos',	40,	10,	NULL,	'S'),
(16,	'Itens',	'assistencia-tecnica/itens',	50,	10,	NULL,	'S'),
(17,	'Plano de Produção',	'#plano-producao',	15,	NULL,	'ti-settings',	'S'),
(18,	'Consulta',	'plano-producao/consulta',	20,	17,	NULL,	'S'),
(19,	'Usuário x Planejador',	'plano-producao/vinculo',	20,	17,	NULL,	'S'),
(20,	'Painel',	'assistencia-tecnica/painel',	20,	10,	NULL,	'S'),
(21,	'Setores',	'auxiliares/setores',	320,	1,	'',	'S'),
(22,	'Geração de Protocolo ',	'assistencia-tecnica/geracao-protocolo-interno',	40,	10,	NULL,	'S'),
(23,	'Relatórios',	'#assistencia-tecnica/relatorios',	500,	10,	NULL,	'S'),
(24,	'Defeitos por Item',	'assistencia-tecnica/relatorios/defeitos-item',	500,	23,	NULL,	'S'),
(25,	'Comercial - Rede',	'#comercial',	20,	NULL,	'ti-shopping-cart',	'S'),
(26,	'Relatório Visitas',	'comercial/relatorio-visitas',	500,	25,	NULL,	'S'),
(27,	'Cadastros',	'#comercial/cadastros',	500,	25,	NULL,	'S'),
(28,	'Concorrentes',	'comercial/cadastros/concorrentes',	100,	27,	NULL,	'S'),
(30,	'Categorias Concorrentes',	'comercial/cadastros/categoria-concorrentes',	100,	27,	NULL,	'S'),
(31,	'Comercial - Mont.',	'#comercial-montadoras',	25,	NULL,	'ti-shopping-cart',	'S'),
(32,	'Relatório Visitas ',	'comercial-montadoras/relatorio-visitas-montadoras',	500,	31,	NULL,	'S'),
(35,	'TI',	'#ti',	700,	NULL,	'ti-notepad',	'S'),
(36,	'Tipos de Despesas',	'ti/type-expenses',	800,	35,	'',	'S'),
(37,	'Tipos de Horas',	'ti/type-hours',	750,	35,	'',	'S'),
(38,	'Unidades',	'ti/companies',	300,	35,	NULL,	'S'),
(39,	'Chamados',	'ti/tickets',	300,	35,	NULL,	'S'),
(40,	'Painel',	'ti/panel',	400,	35,	NULL,	'S'),
(41,	'Atividades',	'ti/attendance',	350,	35,	NULL,	'S'),
(42,	'Listagem',	'assistencia-tecnica/relatorios/listagem',	500,	23,	NULL,	'S'),
(43,	'Ordem de Serviço',	'assistencia-externa/ordem-servico',	100,	46,	NULL,	'S'),
(44,	'Perfis de Usuário',	'auxiliares/perfis',	310,	1,	'',	'S'),
(45,	'Serviços',	'assistencia-externa/servicos',	200,	46,	NULL,	'S'),
(46,	'Assistência Externa',	'#assistencia-externa',	11,	NULL,	'ti-hummer',	'S'),
(47,	'Lista de Preço por Serviço',	'assistencia-externa/valores-servicos',	200,	46,	NULL,	'S'),
(48,	'Categorias Serviço',	'assistencia-externa/categorias-servico',	200,	46,	NULL,	'S'),
(49,	'Lista de Preço por Categoria',	'assistencia-externa/valores-categoria',	200,	46,	NULL,	'S'),
(50,	'Vínculo de Vendedores',	'auxiliares/vinculo-vendedor',	10000,	1,	NULL,	'S'),
(52,	'Encerramento de Ordens',	'assistencia-externa/encerramento-ordens',	600,	46,	NULL,	'S'),
(53,	'Agrupador de Itens',	'assistencia-externa/agrupador-itens',	600,	46,	NULL,	'S'),
(54,	'Portaria',	'#portaria',	25,	NULL,	'ti-car',	'S'),
(55,	'Tipo de Assunto',	'portaria/tipo-assunto',	600,	54,	NULL,	'S'),
(56,	'Veículos',	'portaria/veiculos',	600,	54,	NULL,	'S');

-- 2020-06-23 14:22:30
