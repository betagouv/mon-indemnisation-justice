import { format } from "date-fns";

export const castNumber = (input) => {
  const inputNumber = input.replace(/[^\d]/ig, "");
  return inputNumber;
}

export const castDate = (input) => {
  if(input)
    return format(input,"yyyy-MM-dd");
  return "";
}
