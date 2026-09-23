/*
  De qual área é cada cargo.

  O setor da pessoa pode vir de duas fontes: o token do Portal
  (area_slug) ou a ficha funcional do RH (obras_departamento). Quando
  nenhuma das duas traz, sobra o cargo — e é daí que dá pra deduzir.

  Esta tabela guarda pedaços de texto que, se aparecerem no cargo,
  indicam a área. Ex: cargo "Analista de Segurança do Trabalho Jr"
  contém "seguranca do trabalho", então a pessoa é dessa área.

  A comparação segue o mesmo padrão que course_cargo_keywords já usava:
  o cargo é normalizado (sem acento, minúsculo) e a palavra-chave é
  procurada dentro dele. Por isso a palavra deve ser cadastrada já
  normalizada — a tela de administração cuida disso.

  Ordem de prioridade no login, da mais confiável pra menos:
    1. obras_departamento (setor oficial do RH)
    2. area_slug (token do Portal)
    3. esta tabela (deduzido do cargo)

  COMO RODAR

  Pelo HeidiSQL ou phpMyAdmin: selecione o banco, abra a aba SQL, cole
  este arquivo inteiro e execute.
*/

CREATE TABLE IF NOT EXISTS cargo_areas (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  palavra    VARCHAR(80)  NOT NULL,
  area_id    INT UNSIGNED NOT NULL,
  created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cargo_areas (palavra, area_id),
  KEY idx_cargo_areas_area (area_id),
  CONSTRAINT fk_cargo_areas_area FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
