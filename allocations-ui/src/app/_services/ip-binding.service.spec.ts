import { TestBed } from '@angular/core/testing';

import { IpBindingService } from './ip-binding.service';

describe('IpBindingService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: IpBindingService = TestBed.get(IpBindingService);
    expect(service).toBeTruthy();
  });
});
