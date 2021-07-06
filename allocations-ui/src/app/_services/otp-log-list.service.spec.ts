import { TestBed } from '@angular/core/testing';

import { OtpLogListService } from './otp-log-list.service';

describe('OtpLogListService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: OtpLogListService = TestBed.get(OtpLogListService);
    expect(service).toBeTruthy();
  });
});
