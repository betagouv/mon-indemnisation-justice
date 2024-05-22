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
  if(!a)
    return true;
  return (a.length == 0);
}

export const check_equal = (a,b) => {
  if((!check_empty(a)&&check_empty(b))||(check_empty(a)&&!check_empty(b)))
    return false;
  return (a == b);
}

export const state_error_if_false = (test) => {
  return (false == test) ? STATE_ERROR : "";
}

export const check_numbers = (a,ln) => {
  if(!a)
    return false;
  return (a.replaceAll(/[^\d]/gi,'').length>=ln);
}

export const check_min_length = (a,ln) => {
  if(!a)
    return false;
  return (a.length>=ln);
}
