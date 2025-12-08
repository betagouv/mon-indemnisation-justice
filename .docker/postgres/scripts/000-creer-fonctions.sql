CREATE OR REPLACE FUNCTION nb_jours_ouvres(from_date date, to_date date)
RETURNS BIGINT
AS $fbd$
    SELECT count(d::date) AS d
    FROM generate_series(from_date, to_date, '1 day'::interval) d
    WHERE extract('dow' FROM d) NOT IN (0, 6)
$fbd$ LANGUAGE sql;

CREATE OR REPLACE FUNCTION initiales(full_name TEXT)
RETURNS TEXT AS $$
DECLARE
    result TEXT :='';
    part VARCHAR :='';
BEGIN
    FOREACH part IN ARRAY string_to_array($1, ' ') LOOP
        result :=  result || substr(part, 1, 1);
    END LOOP;

    RETURN result;
END;
$$ LANGUAGE plpgsql;