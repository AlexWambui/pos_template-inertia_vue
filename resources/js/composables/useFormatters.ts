export function useFormatters() {
  const formatNumber = (value: number): string => {
    return new Intl.NumberFormat().format(value);
  };

  const formatCurrency = (value: number, currency: string = 'PHP'): string => {
    return new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: currency,
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(value);
  };

  const formatDecimal = (value: number): string => {
    return new Intl.NumberFormat('en-PH', {
      style: 'decimal',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(value);
  };

  const formatCompact = (value: number): string => {
    return new Intl.NumberFormat('en', {
      notation: 'compact',
      compactDisplay: 'short',
    }).format(value);
  };

  return {
    formatNumber,
    formatCurrency,
    formatDecimal,
    formatCompact,
  };
}