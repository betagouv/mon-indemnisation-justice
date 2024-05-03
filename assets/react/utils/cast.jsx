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

export const castUrl = (input) => {
  if(!input)
    return "";
  return input;
}

export const formatUrl=(input) => {
  if(!input)
    return null;
  return input;
}

export const formatDate=(input) => {
  if(!input)
    return null;
  return input;
}

export const checkDate=(date1,date2) => {
  return castDate(date1??"") === castDate(date2??"");
}

export const checkString=(str1,str2) => {
  return (str1??"") === (str2??"");
}

export const checkUrl=(url1,url2) => {
  return castUrl(url1??"") === castUrl(url2??"");
}
