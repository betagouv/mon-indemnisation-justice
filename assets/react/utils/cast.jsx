export const castNumber = (input) => {
  const inputNumber = input.replace(/[^\d]/ig, "");
  return inputNumber;
}
