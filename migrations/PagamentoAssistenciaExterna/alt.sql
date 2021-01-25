CREATE OR REPLACE FORCE EDITIONABLE VIEW "VSDI_ORDENS_SERV_PAGAMENTOS" ("ID", "ORDEM", "CLIENTE_ID", "CRIADO_POR", "CRIADO_EM_CHAR", "CRIADO_EM", "DESCRICAO", "CNPJ_CPF", "ENDERECO", "NUMERO", "CIDADE", "UF", "BAIRRO", "TELEFONE", "CEP", "EMAIL", "COMPLEMENTO", "DESCRICAO_STATUS", "STATUS_ID", "LABEL", "VALOR") AS
SELECT ord.id,
       LPAD(ord.num_ordem,6,'0') ordem,
       ord.id,
       ord.criado_por,
       TO_CHAR(ord.criado_em,'DD/MM/RRRR') criado_em_char,
       ord.criado_em,
       cli.nome,
       cli.cpf_cnpj,
       cli.endereco,
       cli.nro,
       cli.cidade,
       cli.uf,
       cli.bairro,
       cli.telefone,
       cli.cep,
       cli.e_mail,
       cli.complemento,
             (SELECT status.descricao
               FROM TSDI_ASSIST_CAD_STATUS status
              WHERE status.ID =
                       (SELECT hist.STATUS_ID
                          FROM tsdi_assist_ext_status hist
                         WHERE hist.ID =
                                  (SELECT MAX (hist1.ID)
                                     FROM tsdi_assist_ext_status hist1
                                    WHERE hist1.ordem_id = ord.ID))) descricao_status,
        (SELECT status.id
               FROM TSDI_ASSIST_CAD_STATUS status
              WHERE status.ID =
                       (SELECT hist.STATUS_ID
                          FROM tsdi_assist_ext_status hist
                         WHERE hist.ID =
                                  (SELECT MAX (hist1.ID)
                                     FROM tsdi_assist_ext_status hist1
                                    WHERE hist1.ordem_id = ord.ID))) status_id ,
    (SELECT status.label
               FROM TSDI_ASSIST_CAD_STATUS status
              WHERE status.ID =
                       (SELECT hist.STATUS_ID
                          FROM tsdi_assist_ext_status hist
                         WHERE hist.ID =
                                  (SELECT MAX (hist1.ID)
                                     FROM tsdi_assist_ext_status hist1
                                    WHERE hist1.ordem_id = ord.ID))) label,
                                    0 VALOR
 FROM tsdi_assist_ext_ordens_serv ord
INNER JOIN tsdi_assistencia_clientes cli ON (cli.id = ord.cliente_assistencia_id)
WHERE NOT EXISTS ( SELECT 1 FROM tsdi_pagamento_ordem pgo WHERE pgo.ordem_id = ord.id)
ORDER BY ord.id DESC;

CREATE TABLE "TSDI_PAGAMENTO_ORDEM"
   (	"ID" NUMBER NOT NULL ENABLE,
	"PAGAMENTO_ID" NUMBER NOT NULL ENABLE,
	"ORDEM_ID" NUMBER NOT NULL ENABLE,
	"VALOR" NUMBER(15,2) NOT NULL ENABLE,
	 CONSTRAINT "TSDI_PAGAMENTO_ORDEM_PK" PRIMARY KEY ("ID"),
     CONSTRAINT "TSDI_PAGAMENTO_ORDEM_PAGAMENTO_FK"
     FOREIGN KEY ("PAGAMENTO_ID")
     REFERENCES "TSDI_PAGAMENTO" ("ID")
     ON DELETE CASCADE
     );




  CREATE OR REPLACE FORCE EDITIONABLE VIEW "VSDI_PAGAMENTOS_ATE" ("ID", "NUM_PGTO", "DT_PAGAMENTO", "RESPONSAVEL_ID", "VALOR_TOTAL", "ASSISTENCIA_ID", "AUTORIZADO_EM", "EXISTE_ANEXO") AS
SELECT pg.id,
       LPAD(PG.ID, 6, '0') NUM_PGTO,
       TO_CHAR(PG.DT_PAGAMENTO, 'DD/MM/RRRR HH24:MI:SS') DT_PAGAMENTO,
       pg.responsavel_id,

  (SELECT sum(pgo.valor)
   FROM tpagamento_ordem pgo
   WHERE pgo.pagamento_id = pg.id) valor_total,

  (SELECT ord.criado_por
   FROM tsdi_pagamento_ordem pgo,
        tsdi_assist_ext_ordens_serv ord
   WHERE pgo.ordem_id = ord.id
     AND rownum = 1) assistencia_id,
       pg.autorizado_em,
  (SELECT max(1)
   FROM tsdi_pagamento_arquivo arq
   WHERE arq.pagamento_id = pg.id) existe_anexo
FROM tsdi_pagamento pg;



  CREATE TABLE "TSDI_PAGAMENTO_ARQUIVO"
   (	"ID" NUMBER NOT NULL ENABLE,
	"PAGAMENTO_ID" NUMBER NOT NULL ENABLE,
	"TIPO" VARCHAR2(400) NOT NULL ENABLE,
	"ARQUIVO" VARCHAR2(400) NOT NULL ENABLE,
	 CONSTRAINT "TSDI_PAGAMENTO_ARQUIVO_PK" PRIMARY KEY ("ID"),
      CONSTRAINT "TSDI_PAGAMENTO_ARQUIVO_PAGAMENTO_FK"
     FOREIGN KEY ("PAGAMENTO_ID")
     REFERENCES "TSDI_PAGAMENTO" ("ID")
     ON DELETE CASCADE);



  CREATE OR REPLACE FORCE EDITIONABLE VIEW "VSDI_PAGAMENTOS_ATE_ORDENS" ("ID", "ORDEM", "CRIADO_EM", "ORDEM_ID", "PAGAMENTO_ID", "VALOR") AS
  SELECT pgo.id, LPAD(ord.num_ordem,6,0) ordem, TO_CHAR(ord.criado_em,'DD/MM/RRRR') criado_em, pgo.ordem_id,pgo.pagamento_id, 10 valor
  FROM tsdi_pagamento_ordem pgo
INNER JOIN tsdi_assist_ext_ordens_serv ord ON (ord.id = pgo.ordem_id);



  CREATE TABLE "TSDI_PAGAMENTO"
   (	"ID" NUMBER NOT NULL ENABLE,
	"DT_PAGAMENTO" DATE NOT NULL ENABLE,
	"CRIADO_POR" NUMBER NOT NULL ENABLE,
	"CRIADO_EM" DATE NOT NULL ENABLE,
	"RESPONSAVEL_ID" NUMBER,
	"AUTORIZADO_EM" DATE,
	 CONSTRAINT "TPAGAMENTO_PK" PRIMARY KEY ("ID"));
