import { Controller } from 'stimulus';

import ReactDOM from 'react-dom';
import React from 'react';
import BrisPortes from '../react/components/BrisPortes';

export default class extends Controller {
    connect() {
      ReactDOM.render(
          <BrisPortes />,
          this.element
      )
    }
}
