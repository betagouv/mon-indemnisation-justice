const STATE_SUCCESS = "success";
const STATE_ERROR = "error";

export const getStateOnEmpty = (data) => {
  if(!data || data.length==0)
    return "error";
  return "default";
}

/**
 * Validation d'email
 * @return bool Réussite à l'examen
 */
export const check_email = (email) => {
  let re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
}

export const check_empty = (a) => {
  return !a||(a.length == 0);
}

export const check_equal = (a,b) => {
  if(!b||!a||(b.length == 0)||(a.length==0))
    return true;
  return (a == b);
}

export const state_error_if_false = (test) => {
  return (false == test) ? STATE_ERROR : "";
}

export const check_min_length = (a,ln) => {
  return (a && (a.length>=ln));
}
