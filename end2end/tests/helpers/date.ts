export const hier = () => new Date(Date.now() - 24 * 60 * 60 * 1000);

export const formatYmd = (date: Date) => date.toISOString().split('T')[0];