export const getStateOnEmpty = (data) => {
  if(!data || data.length==0)
    return "error";
  return "default";
}
