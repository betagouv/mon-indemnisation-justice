import { dateEtHeureSimple, heureEtMinutesSimple, heureSimple } from "@/common/services/date.ts";
import { describe, expect, it } from "vitest";

describe("date", () => {
  describe(".heureSimple", () => {
    it(" doit savoir formater une date", () => {
      expect(heureSimple(new Date(2023, 0, 1, 12, 30))).toBe("midi");
      expect(
        heureSimple(new Date(2023, 0, 1, 12, 30), { litterale: true }),
      ).toBe("midi");
      expect(
        heureSimple(new Date(2023, 0, 1, 0, 30), { litterale: true }),
      ).toBe("minuit");
      expect(
        heureSimple(new Date(2023, 0, 1, 12, 30), { litterale: false }),
      ).toBe("12h");
      expect(
        heureSimple(new Date(2023, 0, 1, 0, 30), { litterale: false }),
      ).toBe("0h");
      expect(
        heureSimple(new Date(2023, 0, 1, 7, 30), {
          litterale: true,
        }),
      ).toBe("7h");
    });
  });
  describe(".heureEtMinutesSimple", () => {
    it(" doit savoir formater une date", () => {
      expect(heureEtMinutesSimple(new Date("01-12-2023 12:30:00"))).toBe(
        "midi 30",
      );
      expect(
        heureEtMinutesSimple(new Date("01-12-2023 12:30:00"), {
          litterale: false,
        }),
      ).toBe("12h30");
      expect(heureEtMinutesSimple(new Date("01-12-2023 00:30:00"))).toBe(
        "minuit 30",
      );
      expect(
        heureEtMinutesSimple(new Date("01-12-2023 00:30:00"), {
          litterale: false,
        }),
      ).toBe("0h30");
      expect(
        heureEtMinutesSimple(new Date("01-12-2023 07:30:00"), {
          litterale: true,
        }),
      ).toBe("7h30");
      expect(heureEtMinutesSimple(new Date("01-12-2023 07:30:00"))).toBe(
        "7h30",
      );
      expect(
        heureEtMinutesSimple(new Date("01-12-2023 19:30:00"), {
          litterale: true,
        }),
      ).toBe("19h30");
    });
  });
  describe(".dateEtHeureSimple", () => {
    it(" doit savoir formater une date", () => {
      const anneeCourante = new Date().getFullYear();
      expect(
        dateEtHeureSimple(new Date(`01-12-${anneeCourante} 12:30:00`), {
          masquerAnneeSiCourante: true,
          litterale: false,
          avecMinutes: true,
        }),
      ).toBe("12 janvier à 12h30");
      expect(
        dateEtHeureSimple(new Date(`01-12-${anneeCourante} 12:30:00`), {
          masquerAnneeSiCourante: true,
          litterale: true,
          avecMinutes: true,
        }),
      ).toBe("12 janvier à midi 30");
      expect(
        dateEtHeureSimple(new Date(`01-12-${anneeCourante} 12:30:00`), {
          masquerAnneeSiCourante: true,
          litterale: true,
          avecMinutes: false,
        }),
      ).toBe("12 janvier à midi");
      expect(
        dateEtHeureSimple(new Date(`01-12-2023 12:30:00`), {
          masquerAnneeSiCourante: true,
          litterale: false,
          avecMinutes: true,
        }),
      ).toBe("12 janvier 2023 à 12h30");
    });
  });
});
