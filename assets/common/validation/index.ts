import {
  RequerantManagerImpl,
  RequerantManagerInterface,
} from "@/common/services/RequerantManager";
import {
  registerDecorator,
  ValidationOptions,
  ValidationArguments,
  ValidatorConstraint,
  ValidatorConstraintInterface,
} from "class-validator";
import _, { property } from "lodash";
import { container } from "@/common/services";

/**
 * Le validateur IsEqualTo vérifie que la valeur d'un champ correspond à la
 * valeur d'un autre champ `property` de l'objet validé.
 *
 * @param property
 * @param validationOptions
 * @constructor
 */
export function IsEqualTo(
  property: string,
  validationOptions?: ValidationOptions,
) {
  return function (object: Object, propertyName: string) {
    registerDecorator({
      name: "isEqualTo",
      target: object.constructor,
      propertyName: propertyName,
      constraints: [property],
      options: validationOptions,
      validator: {
        validate(value: any, args: ValidationArguments) {
          const [relatedPropertyName] = args.constraints;
          const relatedValue = (args.object as any)[relatedPropertyName];
          return value === relatedValue;
        },
      },
    });
  };
}

/*
@ValidatorConstraint({ async: true })
export class IsEmailAlreadyUsedConstraint
  implements ValidatorConstraintInterface
{
  constructor() {}
  validate(adresse: any, args: ValidationArguments) {
    return UserRepository.findOneByName(userName).then((user) => {
      if (user) return false;
      return true;
    });
  }
}

 */

/**
 * Le validateur IsEmailAlreadyUsed vérifie que l'adresse courriel définie dans
 * le champ n'est pas déjà attribuée à un autre requérant.
 *
 * @param validationOptions
 * @constructor
 */
export function IsEmailAlreadyUsed(validationOptions?: ValidationOptions) {
  return function (object: Object, propertyName: string) {
    const debouncedVerification = _.debounce(
      async (adresse: string) =>
        container
          .get<RequerantManagerInterface>(RequerantManagerImpl)
          .estAdresseCourrielAttribuee(adresse),
      250,
    );

    registerDecorator({
      name: "isEmailAlreadyUsed",
      target: object.constructor,
      propertyName: propertyName,
      constraints: [property],
      async: true,
      options: validationOptions,
      validator: {
        async validate(value: any, args: ValidationArguments) {
          return await container
            .get<RequerantManagerInterface>(RequerantManagerImpl)
            .estAdresseCourrielAttribuee(value);
        },
      },
    });
  };
}
