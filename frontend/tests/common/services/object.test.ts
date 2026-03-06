import { differentiel, fusion } from "@/common/services/object";
import { describe, expect, it } from "vitest";

describe("differentiel", () => {
  it("doit retourner undefined pour 2 valeurs primitives identiques", () => {
    expect(differentiel(5, 5)).toBeUndefined();
    expect(differentiel("hello", "hello")).toBeUndefined();
    expect(differentiel(true, true)).toBeUndefined();
    expect(differentiel(null, null)).toBeUndefined();
    expect(differentiel(undefined, undefined)).toBeUndefined();
  });

  it("doit retourner b pour 2 valeurs primitives différentes", () => {
    expect(differentiel(5, 10)).toBe(10);
    expect(differentiel("hello", "world")).toBe("world");
    expect(differentiel(true, false)).toBe(false);
    expect(differentiel(null, "value")).toBe("value");
    expect(differentiel(undefined, 42)).toBe(42);
  });

  it("doit retourner b pour 2 valeurs de types différents", () => {
    expect(differentiel(5, "5")).toBe("5");
    expect(differentiel("hello", 123)).toBe(123);
    expect(differentiel(null, {})).toEqual({});
    expect(differentiel([], "array")).toBe("array");
  });

  it("doit détecter les tableaux similaires", () => {
    expect(differentiel([1, 2, 3], [1, 2, 3])).toBeUndefined();
    expect(differentiel(["a", "b"], ["a", "b"])).toBeUndefined();
    expect(differentiel([], [])).toBeUndefined();
  });

  it("doit retourner b pour 2 tableaux différentes", () => {
    expect(differentiel([1, 2], [1, 3])).toEqual([1, 3]);
    expect(differentiel([1, 2], [1, 2, 3])).toEqual([1, 2, 3]);
    expect(differentiel(["a", "b"], ["b", "a"])).toEqual(["b", "a"]);
    expect(differentiel([], [1])).toEqual([1]);
  });

  it("doit détecter les object similaires", () => {
    expect(differentiel({}, {})).toBeUndefined();
    expect(differentiel({ a: 1, b: 2 }, { a: 1, b: 2 })).toBeUndefined();
    expect(
      differentiel(
        { nested: { value: "test" } },
        { nested: { value: "test" } },
      ),
    ).toBeUndefined();
  });

  it("doit détecter les différences dans les objets", () => {
    expect(differentiel({ a: 1 }, { a: 2 })).toEqual({ a: 2 });
    expect(differentiel({ a: 1 }, { a: 1, b: 2 })).toEqual({ b: 2 });
    expect(differentiel({ a: 1, b: 2 }, { a: 1 })).toBeUndefined();
    expect(
      differentiel({ nested: { value: "old" } }, { nested: { value: "new" } }),
    ).toEqual({ nested: { value: "new" } });
  });

  it("doit gérer les cas de structures profondes complexes", () => {
    const a = {
      name: "John",
      lastname: "Doe",
      age: 30,
      address: {
        street: "123 Main St",
        city: "New York",
      },
      hobbies: ["reading", "swimming"],
    };

    const b = {
      name: "John",
      age: 31,
      address: {
        street: "123 Main St",
        city: "Boston",
      },
      hobbies: ["reading", "swimming", "hiking"],
      telephone: "555-1234",
    };

    expect(differentiel(a, b)).toEqual({
      age: 31,
      address: {
        city: "Boston",
      },
      hobbies: ["reading", "swimming", "hiking"],
      telephone: "555-1234",
    });

    expect(
      differentiel(a, {
        age: 30,
        address: {
          street: "123 Main St",
          city: "New York",
        },
      }),
    ).toBeUndefined();
  });

  it("doit gérer la comparaison entre tableau et objet", () => {
    expect(differentiel([1, 2], { 0: 1, 1: 2 })).toEqual({ 0: 1, 1: 2 });
    expect(differentiel({ a: 1 }, ["a", 1])).toEqual(["a", 1]);
  });
});

describe("fusion", () => {
  it("doit retourner source quand la cible est nullish", () => {
    expect(fusion(null, { a: 1 })).toEqual({ a: 1 });
    expect(fusion(undefined, { a: 1 })).toEqual({ a: 1 });
  });

  it("doit retourner cible quand la source est nullish", () => {
    expect(fusion({ a: 1 }, null)).toEqual({ a: 1 });
    expect(fusion({ a: 1 }, undefined)).toEqual({ a: 1 });
  });

  it("doit écraser les types primitifs divergents", () => {
    expect(fusion(5, 10)).toBe(10);
    expect(fusion("hello", "world")).toBe("world");
    expect(fusion(true, false)).toBe(false);
  });

  it("doit écraser les tableaux divergents", () => {
    expect(fusion([1, 2, 3], [4, 5])).toEqual([4, 5]);
    expect(fusion(["a", "b"], ["c"])).toEqual(["c"]);
  });

  it("doit fusionner les objets simples", () => {
    expect(fusion({ a: 1 }, { b: 2 })).toEqual({ a: 1, b: 2 });
    expect(fusion({ a: 1 }, { a: 2 })).toEqual({ a: 2 });
    expect(fusion({}, { a: 1, b: 2 })).toEqual({ a: 1, b: 2 });
  });

  it("doit fusionner en profondeur les objets complexes", () => {
    const target = {
      a: 1,
      nested: {
        value: "old",
        deep: {
          prop: "original",
        },
      },
    };

    const source = {
      b: 2,
      nested: {
        value: "new",
        extra: "added",
      },
    };

    expect(fusion(target, source)).toEqual({
      a: 1,
      b: 2,
      nested: {
        value: "new",
        deep: {
          prop: "original",
        },
        extra: "added",
      },
    });
  });

  it("doit fusionner en profondeur les objets de structures profonde complexe", () => {
    const target = {
      name: "John",
      age: 30,
      address: {
        street: "123 Main St",
        city: "New York",
        coordinates: {
          lat: 40.7128,
          lng: -74.006,
        },
      },
      hobbies: ["reading", "swimming"],
      metadata: {
        created: "2023-01-01",
      },
    };

    const source = {
      age: 31,
      address: {
        city: "Boston",
        coordinates: {
          lat: 42.3601,
        },
      },
      hobbies: ["hiking", "cycling"],
      metadata: {
        updated: "2023-06-15",
      },
    };

    expect(fusion(target, source)).toEqual({
      name: "John",
      age: 31,
      address: {
        street: "123 Main St",
        city: "Boston",
        coordinates: {
          lat: 42.3601,
          lng: -74.006,
        },
      },
      hobbies: ["hiking", "cycling"],
      metadata: {
        created: "2023-01-01",
        updated: "2023-06-15",
      },
    });
  });

  it("doit gérer la fusion entre tableau et objet", () => {
    expect(fusion({ a: 1 }, [1, 2])).toEqual([1, 2]);
    expect(fusion([1, 2], { 0: 3, 1: 4 })).toEqual({ 0: 3, 1: 4 });
  });

  it("ne doit pas merger quand la cible n'est pas un objet", () => {
    expect(fusion("string", { a: 1 })).toEqual({ a: 1 });
    expect(fusion(42, { a: 1 })).toEqual({ a: 1 });
    expect(fusion(true, { a: 1 })).toEqual({ a: 1 });
  });
});
